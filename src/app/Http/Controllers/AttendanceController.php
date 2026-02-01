<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceCorrectRequest;
use App\Models\CorrectionAttendanceDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // トランザクション用

class AttendanceController extends Controller
{
    /**
     * 打刻画面の表示（ステータス判定）
     */
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // 今日の最新の勤怠データを取得
        $attendance = $user->attendances()->where('date', $today)->latest()->first();

        // 初期ステータスは「勤務外」
        $status = 'out';

        if ($attendance) {
            if ($attendance->check_out) {
                $status = 'finished'; // 退勤済み
            } else {
                // 休憩中かどうかの判定（最新の休憩に終了時間が入っていない場合）
                $latestRest = $attendance->rests()->latest()->first();
                if ($latestRest && !$latestRest->end_time) {
                    $status = 'resting'; // 休憩中
                } else {
                    $status = 'working'; // 出勤中
                }
            }
        }

        // Bladeにステータスを渡す
        return view('attendance.index', compact('status'));
    }

    // 各打刻アクションのメソッドをここに定義

    /**
     * 出勤アクション (FN020)
     */
    public function checkIn()
    {
        $user = Auth::user();
        $today = Carbon::today()->toDateString();

        // ❗ すでに今日出勤済みなら、二重登録させない (FN020-2)
        if ($user->attendances()->where('date', $today)->exists()) {
            return redirect()->back()->with('error', 'すでに出勤済みです');
        }

        // 勤怠レコードの作成
        $user->attendances()->create([
            'date' => $today,
            'check_in' => Carbon::now()->toTimeString(),
        ]);

        return redirect()->back();
    }

    /**
     * 休憩開始アクション (FN021)
     */
    public function restStart()
    {
        $user = Auth::user();
        // 今日の勤怠レコードを取得
        $attendance = $user->attendances()->where('date', Carbon::today()->toDateString())->first();

        if ($attendance) {
            // restsテーブルに新しいレコードを作成
            $attendance->rests()->create([
                'start_time' => Carbon::now()->toTimeString(),
            ]);
        }

        return redirect()->back();
    }

    /**
     * 休憩終了アクション (FN021)
     */
    public function restEnd()
    {
        $user = Auth::user();
        $attendance = $user->attendances()->where('date', Carbon::today()->toDateString())->first();

        if ($attendance) {
            // まだ終了時間が空（null）の最新の休憩レコードを探す
            $latestRest = $attendance->rests()->whereNull('end_time')->latest()->first();

            if ($latestRest) {
                $latestRest->update([
                    'end_time' => Carbon::now()->toTimeString(),
                ]);
            }
        }

        return redirect()->back();
    }

    /**
     * 退勤アクション (FN022)
     */
    public function checkOut()
    {
        $user = Auth::user();
        $attendance = $user->attendances()->where('date', Carbon::today()->toDateString())->first();

        // 退勤時刻を記録
        $attendance->update([
            'check_out' => Carbon::now()->toTimeString(),
        ]);

        return redirect()->back()->with('message', 'お疲れ様でした！');
    }

    /**
     * 自分の勤怠一覧画面を表示 (FN023, FN024)
     */
    public function attendanceList(Request $request)
    {
        $user = Auth::user();

        // URLのパラメータから年月を取得（例: 2023-06）、なければ今月
        $monthParam = $request->query('month', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::parse($monthParam);

        // パースする際に、その月の「1日」として扱うように明示
        $currentDate = Carbon::parse($monthParam)->startOfMonth();

        // 1. デザイン用の「2023/06」形式を作成
        $displayDate = $currentDate->format('Y/m');

        // 2. データベース検索用の期間指定(N+1問題解消のため with('rests') を追加)
        $attendances = $user->attendances()
            ->with('rests')
            ->whereYear('date', $currentDate->year)
            ->whereMonth('date', $currentDate->month)
            ->orderBy('date', 'asc')
            ->get();

        // 3. リンク用のデータ（内部処理は Y-m 形式が扱いやすい）
        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');

        // 4. 【追加】その月の全日付リストを作成 (CarbonPeriodを使用)
        // startOfMonth() から endOfMonth() までの1日刻みのリスト
        $period = \Carbon\CarbonPeriod::create(
            $currentDate->copy()->startOfMonth(),
            $currentDate->copy()->endOfMonth()
        )->toArray();

        return view('attendance.list', compact(
            'attendances',
            'displayDate',
            'prevMonth',
            'nextMonth',
            'period' // Bladeに渡す
        ));
    }

    /**
     * 勤怠詳細画面（修正申請フォーム）の表示 (FN026, FN027)
     */
    public function showDetail($id)
    {
        $user = Auth::user();
        // 指定された勤怠データを取得（リレーションで休憩データもロード）
        $attendance = Attendance::with('rests')->findOrFail($id);

        // すでに「承認待ち」の申請があるか確認 (FN027)
        $isPending = AttendanceCorrectRequest::where('attendance_id', $id)
            ->where('status', 'pending')
            ->exists();

        return view('attendance.detail', compact('attendance', 'user', 'isPending'));
    }
}

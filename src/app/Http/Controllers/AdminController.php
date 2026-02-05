<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest; // 申請モデル
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // トランザクション用
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;

class AdminController extends Controller
{
    /**
     * 管理者ログイン処理
     */
    public function login(LoginRequest $request)
    {
        // 1. バリデーションは LoginRequest で完結しているため、ここではデータを取り出すだけ
        $credentials = $request->only(['email', 'password']);

        // 2. 認証試行
        // Auth::attempt は「一致するユーザーがいればログイン状態にする」関数です
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // ログインしたユーザーが管理者（role=1）かチェック
            if (auth()->user()->role === 1) {
                // 管理者なら管理者用の一覧ページへ
                return redirect()->route('admin.attendance.list');
            }

            // 管理者でなければログアウトさせてエラー（一般ユーザーが管理者入口から入るのを防ぐ）
            Auth::logout();
            return back()->withErrors([
                'email' => '管理者権限がありません。',
            ]);
        }

        // 3. 認証失敗時
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }

    /**
     * 【FN034, FN035】全スタッフの日次勤怠一覧を表示
     * デフォルトは当日、前日・翌日の移動機能付き
     */
    public function dailyAttendance(Request $request)
    {
        $dateParam = $request->query('date', Carbon::today()->toDateString());
        $currentDate = Carbon::parse($dateParam);

        $attendances = Attendance::with(['user', 'rests'])
            ->where('date', $dateParam)
            ->get();

        return view('admin.attendance.list', [
            'attendances' => $attendances,
            'displayDate' => $currentDate->format('Y/m/d'),
            'prevDate' => $currentDate->copy()->subDay()->toDateString(),
            'nextDate' => $currentDate->copy()->addDay()->toDateString(),
        ]);
    }

    /**
     * 【FN037, FN038】管理者用：勤怠詳細画面の表示
     */
    public function showDetail(Request $request, $id) // Request を追加
    {
        // findOrFail ではなく find にして、データがなくても落ちないようにする
        $attendance = Attendance::with(['user', 'rests'])->find($id);

        // データがない場合、URLから日付を取得、それもなければ今日にする
        $displayDate = $attendance ? $attendance->date : $request->query('date', now()->toDateString());

        return view('admin.attendance.detail', compact('attendance', 'displayDate'));
    }

    /**
     * 【FN039, FN040】管理者用：勤怠情報の直接修正
     */
    public function update(Request $request, $id)
    {
        // 1. バリデーション
        $request->validate([
            'check_in'  => 'required',
            'check_out' => 'required|after:check_in',
            'remark'    => 'required',
        ], [
            'check_out.after' => '出勤時間もしくは退勤時間が不適切な値です', // FN039
            'remark.required' => '備考を記入してください', // FN039
        ]);

        DB::transaction(function () use ($request, $id) {
            $attendance = Attendance::findOrFail($id);

            // 2. 勤怠本体の更新 (FN040)
            $attendance->update([
                'check_in'  => $request->check_in,
                'check_out' => $request->check_out,
                // 備考を保存するカラム（例: remark）がある場合
                'remark'    => $request->remark,
            ]);

            // 3. 既存の休憩データの更新
            if ($request->has('rests')) {
                foreach ($request->rests as $restId => $times) {
                    // 開始・終了の両方が入力されている場合のみ更新
                    if ($times['start'] && $times['end']) {
                        \App\Models\Rest::where('id', $restId)->update([
                            'start_time' => $times['start'],
                            'end_time'   => $times['end'],
                        ]);
                    }
                }
            }

            // 4. 新規休憩データの直接保存
            // new_rest の入力がある場合のみ、新しい休憩レコードを作成する
            if ($request->filled('new_rest.start') && $request->filled('new_rest.end')) {
                $attendance->rests()->create([
                    'start_time' => $request->new_rest['start'],
                    'end_time' => $request->new_rest['end'],
                ]);
            }
        });

        return redirect()->route('admin.attendance.list')->with('message', '勤怠情報を修正しました');
    }

    /**
     * 【FN041, FN042】スタッフ一覧を表示
     * 全一般ユーザーの氏名・メールアドレスを表示
     */
    public function staffList()
    {
        $users = User::where('role', 0)->orderBy('id', 'asc')->get();
        return view('admin.staff.list', compact('users'));
    }

    /**
    * 【FN043, FN044, FN046】スタッフ毎の月次勤怠一覧を表示
    */
    public function staffMonthlyAttendance(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 1. 表示する月の決定（パラメータがなければ今月の1日としてパース）
        $monthParam = $request->query('month', Carbon::today()->format('Y-m'));
        $currentDate = Carbon::parse($monthParam)->startOfMonth();

        // 2. 勤怠データの取得（リレーション含め、日付をCarbonオブジェクトにしておく）
        $attendances = Attendance::with('rests')
            ->where('user_id', $user->id)
            ->whereYear('date', $currentDate->year)
            ->whereMonth('date', $currentDate->month)
            ->get()
            ->map(function ($attendance) {
                // Bladeの firstWhere でマッチさせるため、dateをCarbonの「その日の開始時刻」にする
                $attendance->date = Carbon::parse($attendance->date)->startOfDay();
                return $attendance;
            });

        // 3. その月の全日付リストを作成 (AttendanceControllerの手法に合わせる)
        $period = \Carbon\CarbonPeriod::create(
            $currentDate->copy()->startOfMonth(),
            $currentDate->copy()->endOfMonth()
        )->toArray();

        // 4. 表示用データ
        $displayDate = $currentDate; // Blade側で ->format('Y/m') できるようにCarbonのまま渡す

        return view('admin.staff.attendance_list', compact(
            'user',
            'attendances',
            'displayDate',
            'period'
        ));
    }

    /**
     * CSV出力ロジック (FN045)
     */
    public function exportCsv(Request $request, $id)
    {
        // ここにCSVダウンロードのロジックを後で記述します
        $user = User::findOrFail($id);
        $monthParam = $request->query('month');
        $displayDate = $monthParam ? Carbon::parse($monthParam) : Carbon::today();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $displayDate->year)
            ->whereMonth('date', $displayDate->month)
            ->orderBy('date', 'asc')
            ->get();

        // CSVファイル名の作成 (例: 2023-06_西伶奈_勤怠.csv)
        $fileName = $displayDate->format('Y-m') . '_' . $user->name . '_勤怠.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($attendances) {
            $file = fopen('php://output', 'w');

            // 文字化け防止（Excel用BOM追加）
            fputs($file, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF));

            // ヘッダー（1行目）
            fputcsv($file, ['日付', '出勤時間', '退勤時間', '休憩合計', '合計勤務時間']);

            // データ（2行目以降）
            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    Carbon::parse($attendance->date)->format('Y/m/d'),
                    $attendance->check_in ? Carbon::parse($attendance->check_in)->format('H:i') : '',
                    $attendance->check_out ? Carbon::parse($attendance->check_out)->format('H:i') : '',
                    $attendance->total_rest_time,    // アクセサを使用
                    $attendance->total_working_time // アクセサを使用
                ]);
            }
            fclose($file);
        };

        // ❗ json ではなく stream
        return response()->stream($callback, 200, $headers);
    }
}
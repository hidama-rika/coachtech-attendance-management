<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest; // 申請モデル
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // トランザクション用

class AdminController extends Controller
{
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
    public function showDetail($id)
    {
        // 指定された勤怠データを取得（休憩データも含む）
        $attendance = Attendance::with(['user', 'rests'])->findOrFail($id);

        return view('admin.attendance.detail', compact('attendance'));
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
        $users = User::where('role', 0)->get();
        return view('admin.staff.list', compact('users'));
    }

    /**
     * 【FN043, FN044, FN046】スタッフ毎の月次勤怠一覧を表示
     * 特定ユーザーの月次勤怠、前月・翌月移動
     */
    public function staffMonthlyAttendance(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentDate = Carbon::parse($month);

        $attendances = $user->attendances()
            ->whereYear('date', $currentDate->year)
            ->whereMonth('date', $currentDate->month)
            ->orderBy('date', 'asc')
            ->get();

        return view('admin.staff.attendance_list', [
            'user' => $user,
            'attendances' => $attendances,
            'displayDate' => $currentDate->format('Y/m'),
            'prevMonth' => $currentDate->copy()->subMonth()->format('Y-m'),
            'nextMonth' => $currentDate->copy()->addMonth()->format('Y-m'),
        ]);
    }
}
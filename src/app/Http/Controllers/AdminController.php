<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest; // 申請モデル
use Carbon\Carbon;

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

    /**
     * 【FN047, FN048, FN049】修正申請一覧（承認待ち・承認済み）
     * 全スタッフの申請をステータス別に表示
     */
    public function requestList()
    {
        $pendingRequests = AttendanceCorrectRequest::with(['user', 'attendance'])
            ->where('status', 'pending')->get();
        $approvedRequests = AttendanceCorrectRequest::with(['user', 'attendance'])
            ->where('status', 'approved')->get();

        return view('admin.request.list', compact('pendingRequests', 'approvedRequests'));
    }

    /**
     * 【FN050, FN051】申請詳細の確認と承認処理
     * 承認により勤怠情報と申請ステータスを更新
     */
    public function approveRequest($requestId)
    {
        $request = AttendanceCorrectRequest::findOrFail($requestId);

        // 勤怠本体の更新（申請された内容を反映）
        $attendance = $request->attendance;
        $attendance->update([
            'check_in' => $request->correctionAttendanceDetail->check_in,
            'check_out' => $request->correctionAttendanceDetail->check_out,
        ]);

        // 申請ステータスを"承認済み"に変更
        $request->update(['status' => 'approved']);

        return redirect()->route('admin.request.list')->with('message', '承認しました');
    }
}
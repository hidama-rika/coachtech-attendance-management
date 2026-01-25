<?php

namespace App\Http\Controllers;
namespace App\Http\Requests;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use App\Models\CorrectionAttendanceDetail;
use App\Models\Rest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    /**
     * 【スタッフ側】自分の申請一覧画面 (FN031, FN032)
     */
    public function index()
    {
        $user = Auth::user();

        // 自分の申請を取得（リレーションで勤怠日などを表示）
        $pendingRequests = $user->attendanceCorrectRequests()
            ->where('status', 'pending')->with('attendance')->get();

        $approvedRequests = $user->attendanceCorrectRequests()
            ->where('status', 'approved')->with('attendance')->get();

        return view('request.list', compact('pendingRequests', 'approvedRequests'));
    }

    /**
     * 【スタッフ側】修正申請の保存処理 (FN028, FN030)
     */
    public function store(Request $request, $id)
    {
        // バリデーション（以前作成したFormRequestを使用してもOK）
        $request->validate([
            'check_in' => 'required',
            'check_out' => 'required|after:check_in',
            'remark' => 'required',
        ], [
            'check_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'remark.required' => '備考を記入してください',
        ]);

        DB::transaction(function () use ($request, $id) {
            $user = Auth::user();

            // 修正申請レコードの作成
            $correctRequest = AttendanceCorrectRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $id,
                'status' => 'pending',
            ]);

            // 詳細データの保存
            $correctRequest->correctionAttendanceDetail()->create([
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'remark' => $request->remark,
            ]);
        });

        return redirect()->route('attendance.list')->with('message', '修正申請を出しました');
    }

    /**
     * 【管理者側】全スタッフの申請一覧画面 (FN047, FN048)
     */
    public function adminIndex()
    {
        // 全ユーザーの申請をステータス別に表示
        $pendingRequests = AttendanceCorrectRequest::with(['user', 'attendance'])
            ->where('status', 'pending')->get();

        $approvedRequests = AttendanceCorrectRequest::with(['user', 'attendance'])
            ->where('status', 'approved')->get();

        return view('admin.request.list', compact('pendingRequests', 'approvedRequests'));
    }

    /**
     * 【管理者側】申請詳細の確認画面 (FN050)
     */
    public function adminShow($requestId)
    {
        // 承認待ちの内容を表示
        $request = AttendanceCorrectRequest::with(['user', 'attendance', 'correctionAttendanceDetail'])
            ->findOrFail($requestId);

        return view('admin.request.approve', compact('request'));
    }

    /**
     * 【管理者側】承認処理の実行 (FN051)
     */
    public function approve($requestId)
    {
        $request = AttendanceCorrectRequest::findOrFail($requestId);

        DB::transaction(function () use ($request) {
            // 1. 勤怠本体の更新
            $attendance = $request->attendance;
            $attendance->update([
                'check_in' => $request->correctionAttendanceDetail->check_in,
                'check_out' => $request->correctionAttendanceDetail->check_out,
            ]);

            // 2. 申請ステータスを"承認済み"に変更
            $request->update(['status' => 'approved']);
        });

        return redirect()->route('admin.request.list')->with('message', '承認しました');
    }
}

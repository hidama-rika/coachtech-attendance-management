<?php

namespace App\Http\Controllers;

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
        // // 1. バリデーション (FN028, FN029)（以前作成したFormRequestを使用してもOK）
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

            // 2. 修正申請本体の作成
            $correctRequest = AttendanceCorrectRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $id,
                'status' => 'pending',
            ]);

            // 3. ★【重要】勤怠詳細（出退勤・備考）の保存を追加
            $correctRequest->correctionAttendanceDetail()->create([
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'remark' => $request->remark,
            ]);

            // 4. ★既存の休憩データの修正分をループで保存
            if ($request->has('rests')) {
                foreach ($request->rests as $restId => $times) {
                    if ($times['start'] && $times['end']) {
                        // restDetails() リレーションを使って保存
                        $correctRequest->restDetails()->create([
                            'rest_id' => $restId,
                            'start_time' => $times['start'],
                            'end_time' => $times['end'],
                        ]);
                    }
                }
            }

            // 5. ★新規休憩データ（B案分）の保存
            if ($request->filled('new_rest.start') && $request->filled('new_rest.end')) {
                $correctRequest->restDetails()->create([
                    'start_time' => $request->new_rest['start'],
                    'end_time' => $request->new_rest['end'],
                    // rest_id は指定しない（新規のため）
                ]);
            }
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
        // ★修正後の休憩内容（restDetails）も一緒にロードするように追加
        $request = AttendanceCorrectRequest::with(['user', 'attendance', 'correctionAttendanceDetail', 'restDetails'])
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

            // 2. ★休憩データの更新・追加処理（承認時に実際のrestsテーブルに反映）
            foreach ($request->restDetails as $detail) {
                if ($detail->rest_id) {
                    // 既存休憩の更新
                    Rest::find($detail->rest_id)->update([
                        'start_time' => $detail->start_time,
                        'end_time' => $detail->end_time,
                    ]);
                } else {
                    // 新規休憩の追加
                    $attendance->rests()->create([
                        'start_time' => $detail->start_time,
                        'end_time' => $detail->end_time,
                    ]);
                }
            }

            // 3. 申請ステータスを"承認済み"に変更
            $request->update(['status' => 'approved']);
        });

        return redirect()->route('admin.request.list')->with('message', '承認しました');
    }
}

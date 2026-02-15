<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use App\Models\CorrectionAttendanceDetail;
use App\Models\Rest;
use Carbon\Carbon;
use App\Http\Requests\AttendanceCorrectionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RequestController extends Controller
{
    /**
     * 【スタッフ側】自分の申請一覧画面 (FN031, FN032)
     */
    public function index()
    {
        // 追加：もし管理者なら adminIndex メソッドの内容を返す
        if (auth()->user() && auth()->user()->role === 1) {
            return $this->adminIndex();
        }

        $user = Auth::user();

        // 自分の申請を取得（リレーションで勤怠日などを表示）

        // ✅ 承認待ち (1) のデータを取得
        $pendingRequests = $user->attendanceCorrectRequests()
            ->where('status', AttendanceCorrectRequest::STATUS_PENDING)->with('attendance')->get();

        // ✅ 承認済み (2) のデータを取得
        $approvedRequests = $user->attendanceCorrectRequests()
            ->where('status', AttendanceCorrectRequest::STATUS_APPROVED)->with('attendance')->get();

        return view('request.list', compact('pendingRequests', 'approvedRequests'));
    }

    /**
     * 【スタッフ側】修正申請の保存処理 (FN028, FN030)
     */
    public function store(AttendanceCorrectionRequest $request, $id)
    {

        DB::transaction(function () use ($request, $id) {
            $user = Auth::user();

            // 1. 修正申請本体の作成
            $correctRequest = AttendanceCorrectRequest::create([
                'user_id' => $user->id,
                'attendance_id' => $id,
                'status' => AttendanceCorrectRequest::STATUS_PENDING, // ❗ ここで定数を使う
                'reason' => $request->remark, // 備考を申請理由として保存する
            ]);

            // 2. ★【重要】勤怠詳細（出退勤・備考）の保存を追加
            $correctRequest->correctionAttendanceDetail()->create([
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'remark' => $request->remark,
            ]);

            // 3. 既存の休憩データの修正分を保存（キー名を start_time / end_time に統一）
            if ($request->has('rests')) {
                foreach ($request->rests as $restId => $times) {
                    // start_time と end_time の両方が存在する場合のみ保存
                    if (!empty($times['start_time']) && !empty($times['end_time'])) {
                        $correctRequest->restDetails()->create([
                            'rest_id' => $restId,
                            'start_time' => $times['start_time'],
                            'end_time' => $times['end_time'],
                        ]);
                    }
                }
            }

            // 4. 新規休憩データの保存（ここも start_time / end_time に統一）
            if ($request->filled('new_rest.start_time') && $request->filled('new_rest.end_time')) {
                $correctRequest->restDetails()->create([
                    'start_time' => $request->new_rest['start_time'],
                    'end_time' => $request->new_rest['end_time'],
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
            ->where('status', AttendanceCorrectRequest::STATUS_PENDING)->get();

        $approvedRequests = AttendanceCorrectRequest::with(['user', 'attendance'])
            ->where('status', AttendanceCorrectRequest::STATUS_APPROVED)->get();

        return view('admin.request.list', compact('pendingRequests', 'approvedRequests'));
    }

    /**
     * 【管理者側】申請詳細の確認画面 (FN050)
     */
    public function adminShow($attendance_correct_request_id)
    {
        // 承認待ちの内容を表示
        // ★修正後の休憩内容（restDetails）も一緒にロードするように追加
        $request = AttendanceCorrectRequest::with(['user', 'attendance', 'correctionAttendanceDetail', 'restDetails'])
            ->findOrFail($attendance_correct_request_id);

        return view('admin.request.approve', compact('request'));
    }

    /**
     * 【管理者側】承認処理の実行 (FN051)
     */
    public function approve($attendance_correct_request_id)
    {
        $request = AttendanceCorrectRequest::findOrFail($attendance_correct_request_id);

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
            $request->update(['status' => AttendanceCorrectRequest::STATUS_APPROVED]);
        });

        return redirect()->route('request.list')->with('message', '承認しました');
    }
}

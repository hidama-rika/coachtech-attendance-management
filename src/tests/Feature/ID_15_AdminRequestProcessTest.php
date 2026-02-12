<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use App\Models\CorrectionAttendanceDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ID_15_AdminRequestProcessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // 419エラー（CSRF）を回避
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    /**
     * ID 15-1: 「承認待ち」の修正申請が全て表示されている
     */
    public function test_15_1_admin_can_view_all_pending_requests()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['name' => '申請スタッフ', 'role' => 0]);
        $attendance = Attendance::create(['user_id' => $staff->id, 'date' => '2026-02-01', 'check_in' => '09:00']);

        // 承認待ち申請を作成
        AttendanceCorrectRequest::create([
            'user_id' => $staff->id,
            'attendance_id' => $attendance->id,
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
            'reason' => '修正理由A'
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('申請スタッフ');
        $response->assertSee('2026/02/01');
    }

    /**
     * ID 15-2: 「承認済み」の修正申請が全て表示されている
     */
    public function test_15_2_admin_can_view_all_approved_requests()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['name' => '承認済みスタッフ', 'role' => 0]);
        $attendance = Attendance::create(['user_id' => $staff->id, 'date' => '2026-02-02', 'check_in' => '09:00']);

        // 承認済み申請を作成
        AttendanceCorrectRequest::create([
            'user_id' => $staff->id,
            'attendance_id' => $attendance->id,
            'status' => AttendanceCorrectRequest::STATUS_APPROVED,
            'reason' => '承認済み理由'
        ]);

        $response = $this->actingAs($admin)->get('/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('承認済みスタッフ');
        $response->assertSee('2026/02/02');
    }

    /**
     * ID 15-3: 修正申請の詳細内容が正しく表示されている
     */
    public function test_15_3_admin_view_request_detail_correctly()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['name' => '詳細確認スタッフ', 'role' => 0]);
        $attendance = Attendance::create(['user_id' => $staff->id, 'date' => '2026-02-01', 'check_in' => '09:00']);

        $request = AttendanceCorrectRequest::create([
            'user_id' => $staff->id,
            'attendance_id' => $attendance->id,
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
            'reason' => '詳細内容の検証'
        ]);

        // 修正後の予定データ（CorrectionAttendanceDetail）も作成
        $request->correctionAttendanceDetail()->create([
            'check_in' => '10:00',
            'check_out' => '19:00',
            'remark' => '詳細内容の検証'
        ]);

        // 管理者用承認画面へアクセス
        $response = $this->actingAs($admin)->get("/admin/stamp_correction_request/approve/{$request->id}");

        $response->assertStatus(200);
        $response->assertSee('詳細確認スタッフ');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('詳細内容の検証');
    }

    /**
     * ID 15-4: 修正申請の承認処理が正しく行われる
     */
    public function test_15_4_admin_approve_request_successfully()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['role' => 0]);
        // 元の勤怠
        $attendance = Attendance::create(['user_id' => $staff->id, 'date' => '2026-02-01', 'check_in' => '09:00', 'check_out' => '18:00']);

        $request = AttendanceCorrectRequest::create([
            'user_id' => $staff->id,
            'attendance_id' => $attendance->id,
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
            'reason' => '承認実行テスト'
        ]);

        // 修正したい内容
        $request->correctionAttendanceDetail()->create([
            'check_in' => '11:00',
            'check_out' => '20:00',
            'remark' => '承認実行テスト'
        ]);

        // 承認ボタン押下（POSTリクエスト）
        $response = $this->actingAs($admin)->post("/admin/stamp_correction_request/approve/{$request->id}");

        // 申請一覧へリダイレクトされることを確認
        $response->assertRedirect(route('request.list'));

        // 1. 申請ステータスが「承認済み」に更新されているか
        $this->assertEquals(AttendanceCorrectRequest::STATUS_APPROVED, $request->fresh()->status);

        // 2. 元の勤怠データ（Attendance）が修正後の内容に更新されているか
        $this->assertEquals('11:00:00', $attendance->fresh()->check_in);
        $this->assertEquals('20:00:00', $attendance->fresh()->check_out);
    }
}

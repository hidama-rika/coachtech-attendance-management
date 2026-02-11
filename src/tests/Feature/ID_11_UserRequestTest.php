<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ID_11_UserRequestTest extends TestCase
{
    use RefreshDatabase;

    // 419エラー（CSRF）を回避するための設定を追加
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    /**
     * ID 11-1: 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_11_1_validation_check_in_after_check_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-11',
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'check_in' => '18:00',
            'check_out' => '09:00',
            'remark' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors(['check_out']);
    }

    /**
     * ID 11-2: 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_11_2_validation_rest_start_after_check_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-11',
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'check_in' => '09:00',
            'check_out' => '18:00',
            'rests' => [
                'new' => ['start_time' => '19:00', 'end_time' => '20:00']
            ],
            'remark' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors(['rests.new.start_time']);
    }

    /**
     * ID 11-3: 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_11_3_validation_rest_end_after_check_out()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-11',
            'check_in' => '09:00',
            'check_out' => '18:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'check_in' => '09:00',
            'check_out' => '18:00',
            'rests' => [
                'new' => ['start_time' => '16:00', 'end_time' => '19:00']
            ],
            'remark' => 'テスト備考',
        ]);

        $response->assertSessionHasErrors(['rests.new.end_time']);
    }

    /**
     * ID 11-4: 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_11_4_validation_remark_is_required()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-11',
            'check_in' => '09:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'check_in' => '09:00',
            'check_out' => '18:00',
            'remark' => '',
        ]);

        $response->assertSessionHasErrors(['remark']);
    }

    /**
     * ID 11-5: 修正申請処理が実行される
     */
    public function test_11_5_correction_request_is_executed()
    {
        $user = User::factory()->create(['role' => 0]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-11',
            'check_in' => '09:00',
        ]);

        $response = $this->actingAs($user)->post("/attendance/detail/{$attendance->id}", [
            'check_in' => '09:30',
            'check_out' => '18:30',
            'remark' => '修正願い',
        ]);

        $response->assertRedirect(route('attendance.list'));
        $this->assertDatabaseHas('attendance_correct_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
        ]);
    }

    /**
     * ID 11-6: 「承認待ち」にログインユーザーが行った申請が表示されていること
     */
    public function test_11_6_pending_requests_are_displayed()
    {
        $user = User::factory()->create(['name' => '申請太郎', 'role' => 0]);
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-02-01', 'check_in' => '09:00']);

        AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
            'reason' => '理由：承認待ち'
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=pending');

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('申請太郎'); // 名前でチェック
    }

    /**
     * ID 11-7: 「承認済み」に管理者が承認した修正申請が表示されている
     */
    public function test_11_7_approved_requests_are_displayed()
    {
        $user = User::factory()->create(['name' => '承認済み次郎', 'role' => 0]);
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-02-02', 'check_in' => '09:00']);

        AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => AttendanceCorrectRequest::STATUS_APPROVED,
            'reason' => '理由：承認済み'
        ]);

        // タブを切り替えたURLへアクセス
        $response = $this->actingAs($user)->get('/stamp_correction_request/list?tab=approved');

        $response->assertStatus(200);
        $response->assertSee('承認済み');
        $response->assertSee('承認済み次郎');
    }

    /**
     * ID 11-8: 各申請の「詳細」を押下すると勤怠詳細画面に遷移する
     */
    public function test_11_8_navigate_to_detail_from_request_list()
    {
        $user = User::factory()->create(['role' => 0]);
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => '2026-02-01', 'check_in' => '09:00']);

        AttendanceCorrectRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'status' => AttendanceCorrectRequest::STATUS_PENDING,
            'reason' => '遷移テスト'
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');

        // 詳細ボタンのURLが /attendance/detail/{id} になっているか確認
        $response->assertSee("/attendance/detail/{$attendance->id}");
    }
}

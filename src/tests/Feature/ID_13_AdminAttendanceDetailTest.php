<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ID_13_AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // 419エラー（CSRF）を回避
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    /**
     * ID 13-1: 勤怠詳細画面に表示されるデータが選択したものになっている
     */
    public function test_13_1_admin_view_selected_attendance_detail()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['name' => 'テストスタッフ', 'role' => 0]);
        $date = '2026-02-11';

        $attendance = Attendance::create([
            'user_id' => $staff->id,
            'date' => $date,
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
        ]);

        // 管理者用詳細画面へアクセス
        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('テストスタッフ');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /**
     * ID 13-2: 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_13_2_admin_validation_check_in_after_check_out()
    {
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::create([
            'user_id' => User::factory()->create()->id,
            'date' => '2026-02-11',
            'check_in' => '09:00:00',
        ]);

        $response = $this->actingAs($admin)->post("/admin/attendance/{$attendance->id}", [
            'check_in' => '18:00',
            'check_out' => '09:00', // 不正な値
            'remark' => '管理者修正',
        ]);

        // 期待挙動: 「出勤時間もしくは退勤時間が不適切な値です」と表示される
        $response->assertSessionHasErrors(['check_out']);
        $this->assertEquals('出勤時間もしくは退勤時間が不適切な値です', session('errors')->first('check_out'));
    }

    /**
     * ID 13-3: 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_13_3_admin_validation_rest_start_after_check_out()
    {
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::create([
            'user_id' => User::factory()->create()->id,
            'date' => '2026-02-11',
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin)->post("/admin/attendance/{$attendance->id}", [
            'check_in' => '09:00',
            'check_out' => '18:00',
            'rests' => [
                'new' => ['start' => '19:00', 'end' => '20:00'] // 不正な値
            ],
            'remark' => '管理者修正',
        ]);

        // 期待挙動: 「休憩時間が不適切な値です」と表示される
        // ※コントローラー側のバリデーションロジックによりエラーバッグを確認
        $response->assertSessionHasErrors();
    }

    /**
     * ID 13-4: 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される
     */
    public function test_13_4_admin_validation_rest_end_after_check_out()
    {
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::create([
            'user_id' => User::factory()->create()->id,
            'date' => '2026-02-11',
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
        ]);

        $response = $this->actingAs($admin)->post("/admin/attendance/{$attendance->id}", [
            'check_in' => '09:00',
            'check_out' => '18:00',
            'rests' => [
                'new' => ['start' => '17:00', 'end' => '19:00'] // 不正な値
            ],
            'remark' => '管理者修正',
        ]);

        // 期待挙動: 「休憩時間もしくは退勤時間が不適切な値です」と表示される
        $response->assertSessionHasErrors();
    }

    /**
     * ID 13-5: 備考欄が未入力の場合のエラーメッセージが表示される
     */
    public function test_13_5_admin_validation_remark_is_required()
    {
        $admin = User::factory()->create(['role' => 1]);
        $attendance = Attendance::create([
            'user_id' => User::factory()->create()->id,
            'date' => '2026-02-11',
            'check_in' => '09:00:00',
        ]);

        $response = $this->actingAs($admin)->post("/admin/attendance/{$attendance->id}", [
            'check_in' => '09:00',
            'check_out' => '18:00',
            'remark' => '', // 未入力
        ]);

        // 期待挙動: 「備考を記入してください」と表示される
        $response->assertSessionHasErrors(['remark']);
        $this->assertEquals('備考を記入してください', session('errors')->first('remark'));
    }
}

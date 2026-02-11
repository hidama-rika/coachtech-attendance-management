<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ID_10_UserDetailTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 10-1: 勤怠詳細画面の「名前」がログインユーザーの氏名になっている
     */
    public function test_attendance_detail_name_is_login_user()
    {
        $user = User::factory()->create(['name' => 'テスト太郎']);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-01',
            'check_in' => '09:00:00',
        ]);

        // URLは日付(YYYY-MM-DD)形式と仮定。404が出る場合は $attendance->id に書き換えてください
        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        $response->assertStatus(200);
        $response->assertSee('テスト太郎'); // 期待挙動: 名前がログインユーザーの名前になっている
    }

    /**
     * ID 10-2: 勤怠詳細画面の「日付」が選択した日付になっている
     */
    public function test_attendance_detail_date_is_correct()
    {
        $user = User::factory()->create();
        $date = '2026-02-01';
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'check_in' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        // 期待挙動: 日付が選択した日付になっている
        $response->assertSee('2026');
        $response->assertSee('2');
        $response->assertSee('1');
    }

    /**
     * ID 10-3: 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_attendance_detail_times_match_recorded_values()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-01',
            'check_in' => '09:15:00',
            'check_out' => '18:45:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        // 期待挙動: 出勤・退勤時間が一致している
        $response->assertSee('09:15');
        $response->assertSee('18:45');
    }

    /**
     * ID 10-4: 「休憩」にて記されている時間がログインユーザーの打刻と一致している
     */
    public function test_attendance_detail_rest_times_match_recorded_values()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-01',
            'check_in' => '09:00:00',
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:10:00',
            'end_time' => '13:10:00',
        ]);

        $response = $this->actingAs($user)->get("/attendance/detail/{$attendance->id}");

        // 期待挙動: 休憩時間が一致している
        $response->assertSee('12:10');
        $response->assertSee('13:10');
    }
}

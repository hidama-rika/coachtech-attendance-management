<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ID_5_AttendanceStatusTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 5-1: 勤務外の場合、勤怠ステータスが「勤務外」と表示される
     */
    public function test_status_is_off_duty_when_not_started()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外'); // 期待挙動: 画面上の表示が「勤務外」となる
    }

    /**
     * ID 5-2: 出勤中の場合、勤怠ステータスが「出勤中」と表示される
     */
    public function test_status_is_working_after_clock_in()
    {
        $user = User::factory()->create();

        // 出勤データを作成（退勤時間が null の状態）
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'check_in' => Carbon::now()->toTimeString(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('出勤中'); // 期待挙動: 画面上の表示が「出勤中」となる
    }

    /**
     * ID 5-3: 休憩中の場合、勤怠ステータスが「休憩中」と表示される
     */
    public function test_status_is_resting_during_break()
    {
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'check_in' => Carbon::now()->toTimeString(),
        ]);

        // 休憩開始データを作成（休憩終了が null の状態）
        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->toTimeString(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩中'); // 期待挙動: 画面上の表示が「休憩中」となる
    }

    /**
     * ID 5-4: 退勤済の場合、勤怠ステータスが「退勤済」と表示される
     */
    public function test_status_is_finished_after_clock_out()
    {
        $user = User::factory()->create();

        // 退勤済みのデータを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'check_in' => Carbon::now()->subHours(8)->toTimeString(),
            'check_out' => Carbon::now()->toTimeString(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('退勤済'); // 期待挙動: 画面上の表示が「退勤済」となる
    }
}
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ID_6_AttendanceStartTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 6-1: 出勤ボタンが正しく機能し、ステータスが「出勤中」になる
     */
    public function test_attendance_button_works_and_changes_status()
    {
        $user = User::factory()->create();

        // 1. 勤怠打刻画面を開き、「出勤」ボタンがあることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('出勤');

        // 2. 出勤処理を行う（ボタン押下をシミュレート）
        $response = $this->post('/attendance/start');

        // 3. 処理後にステータスが「出勤中」に変わっていることを確認
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * ID 6-2: 出勤は一日一回のみできる（退勤済みの場合は出勤ボタンが表示されない）
     */
    public function test_attendance_button_is_not_displayed_after_clock_out()
    {
        $user = User::factory()->create();

        // 退勤済みの状態を作る
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'check_in' => Carbon::now()->subHours(8)->toTimeString(),
            'check_out' => Carbon::now()->toTimeString(),
        ]);

        // 勤怠打刻画面を開く
        $response = $this->actingAs($user)->get('/attendance');

        // 「出勤」ボタンが表示されていないことを確認
        $response->assertDontSee('出勤');
    }

    /**
     * ID 6-3: 出勤時刻が勤怠一覧画面で確認できる
     */
    public function test_attendance_time_is_recorded_correctly_in_list()
    {
        $user = User::factory()->create();

        // 時刻を固定して出勤
        $startTime = Carbon::create(2026, 2, 11, 9, 0, 0);
        Carbon::setTestNow($startTime);

        $this->actingAs($user)->post('/attendance/start');

        // 勤怠一覧画面を確認
        $response = $this->get('/attendance/list'); // 一覧画面のURLに合わせて調整してください

        // 出勤時刻（09:00）が正確に記録されていることを確認
        $response->assertSee('09:00');

        Carbon::setTestNow();
    }
}
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ID_8_AttendanceEndTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 8-1: 退勤ボタンが正しく機能し、ステータスが「退勤済」になる
     */
    public function test_attendance_end_button_works_and_changes_status()
    {
        $user = User::factory()->create();
        // 出勤済みの状態を作る
        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'check_in' => Carbon::now()->subHours(8)->toTimeString(),
        ]);

        // 1. 勤怠打刻画面を開き、「退勤」ボタンが表示されていることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        // 2. 退勤処理を実行
        $this->post('/attendance/end');

        // 3. 処理後にステータスが「退勤済」に変わっていることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤済');
    }

    /**
     * ID 8-2: 退勤時刻が勤怠一覧画面で確認できる
     */
    public function test_attendance_end_time_is_recorded_correctly_in_list()
    {
        $user = User::factory()->create();

        // 出勤済みの状態（09:00出勤）
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'check_in' => '09:00:00',
        ]);

        // 退勤時刻を18:00に固定して処理を実行
        $endTime = Carbon::now()->setTime(18, 0, 0);
        Carbon::setTestNow($endTime);

        $this->actingAs($user)->post('/attendance/end');

        // 勤怠一覧画面を確認
        $response = $this->actingAs($user)->get('/attendance/list');

        // 退勤時刻（18:00）が正確に記録されていることを確認
        $response->assertSee('18:00');

        Carbon::setTestNow();
    }
}

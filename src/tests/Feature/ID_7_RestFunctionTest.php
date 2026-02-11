<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ID_7_RestFunctionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 7-1: 休憩ボタンが正しく機能し、ステータスが「休憩中」になる
     */
    public function test_rest_start_button_works_and_changes_status()
    {
        $user = User::factory()->create();
        // 出勤済みの状態を作る
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'check_in' => Carbon::now()->subHour()->toTimeString(),
        ]);

        // 1. 画面に「休憩入」ボタンが表示されていることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        // 2. 休憩開始処理を実行
        $this->post('/attendance/rest-start');

        // 3. ステータスが「休憩中」に変わることを確認
        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }

    /**
     * ID 7-2: 休憩は一日に何回でもできる（休憩完了後に再度「休憩入」が出る）
     */
    public function test_rest_start_can_be_done_multiple_times()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'check_in' => '09:00:00',
        ]);

        // 1回目の休憩を完了させる
        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => '10:00:00',
            'end_time' => '10:10:00',
        ]);

        // 再度「休憩入」ボタンが表示されることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');
    }

    /**
     * ID 7-3: 休憩戻ボタンが正しく機能し、ステータスが「出勤中」に戻る
     */
    public function test_rest_end_button_works_and_changes_status()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'check_in' => Carbon::now()->subHours(2)->toTimeString(),
        ]);
        // 休憩中の状態を作る
        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => Carbon::now()->subHour()->toTimeString(),
        ]);

        // 1. 画面に「休憩戻」ボタンが表示されていることを確認
        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');

        // 2. 休憩終了処理を実行
        $this->post('/attendance/rest-end');

        // 3. ステータスが「出勤中」に戻ることを確認
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }

    /**
     * ID 7-4: 休憩戻は一日に何回でもできる（二回目の休憩中も戻れる）
     */
    public function test_rest_end_can_be_done_multiple_times()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'check_in' => '09:00:00',
        ]);

        // 1回目の休憩は完了済み
        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => '10:00:00',
            'end_time' => '10:10:00',
        ]);

        // 2回目の開始を1回目より確実に後にするため、時刻を2時間進める
        Carbon::setTestNow(Carbon::now()->addHours(2));

        // 2回目の休憩を開始（再度 actingAs を指定して確実に POST）
        $this->actingAs($user)->post('/attendance/rest-start');

        // 最新画面を再取得して、内部状態（休憩中）が反映されたHTMLを確認
        $response = $this->actingAs($user)->get('/attendance');

        // 「休憩戻」ボタンが表示されることを確認
        $response->assertSee('休憩戻');

        // テスト終了後に時刻を元に戻す
        Carbon::setTestNow();
    }

    /**
     * ID 7-5: 休憩時刻が勤怠一覧画面で正しく記録されている
     */
    public function test_rest_time_is_recorded_correctly_in_list()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::now()->toDateString(),
            'check_in' => '09:00:00',
        ]);

        // 休憩時間を固定して記録
        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time' => '13:00:00',
        ]);

        // 勤怠一覧画面を確認
        $response = $this->actingAs($user)->get('/attendance/list');

        // 休憩合計時間（1時間 = 01:00）が記録されていることを確認
        $response->assertSee('01:00');
    }
}
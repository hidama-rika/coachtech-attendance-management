<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ID_9_UserListTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 9-1: 勤怠一覧画面に自分が行った勤怠情報が全て表示されている
     */
    public function test_user_can_see_all_their_attendance_data()
    {
        $user = User::factory()->create();

        // 複数の勤怠データを作成
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-01',
            'check_in' => '09:00:00',
            'check_out' => '18:00:00',
        ]);
        Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-02-02',
            'check_in' => '08:30:00',
            'check_out' => '17:30:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertSee('08:30');
    }

    /**
     * ID 9-2: 勤怠一覧画面に現在の月が表示される
     */
    public function test_user_can_see_current_month()
    {
        $user = User::factory()->create();
        $now = Carbon::now();

        $response = $this->actingAs($user)->get('/attendance/list');

        // 現在の月（YYYY/MM形式）が表示されていることを確認
        $response->assertSee($now->format('Y/m'));
    }

    /**
     * ID 9-3: 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_can_navigate_to_previous_month()
    {
        $user = User::factory()->create();
        $lastMonth = Carbon::now()->subMonth();

        // クエリパラメータ等で前月を指定してアクセス（実装に合わせて調整してください）
        $response = $this->actingAs($user)->get('/attendance/list?month=' . $lastMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee($lastMonth->format('Y/m'));
    }

    /**
     * ID 9-4: 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_can_navigate_to_next_month()
    {
        $user = User::factory()->create();
        $nextMonth = Carbon::now()->addMonth();

        $response = $this->actingAs($user)->get('/attendance/list?month=' . $nextMonth->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('Y/m'));
    }

    /**
     * ID 9-5: 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_can_navigate_to_detail_page()
    {
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in' => '09:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        // 「詳細」というリンクが存在することを確認
        $response->assertSee('詳細');

        // 詳細画面への遷移を確認
        $response = $this->get("/attendance/detail/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細'); // 詳細画面特有の文言
    }
}

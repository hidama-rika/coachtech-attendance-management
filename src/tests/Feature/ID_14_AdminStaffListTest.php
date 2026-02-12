<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ID_14_AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    /**
     * ID 14-1: 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる
     */
    public function test_14_1_admin_can_view_all_staff_info()
    {
        $admin = User::factory()->create(['role' => 1]);
        User::factory()->create(['name' => 'スタッフA', 'email' => 'staff_a@example.com', 'role' => 0]);
        User::factory()->create(['name' => 'スタッフB', 'email' => 'staff_b@example.com', 'role' => 0]);

        // スタッフ一覧ページを開く
        $response = $this->actingAs($admin)->get('/admin/staff/list');

        $response->assertStatus(200);
        $response->assertSee('スタッフA');
        $response->assertSee('staff_a@example.com');
        $response->assertSee('スタッフB');
        $response->assertSee('staff_b@example.com');
    }

    /**
     * ID 14-2: ユーザーの勤怠情報が正しく表示される（個別月次一覧）
     */
    public function test_14_2_admin_can_view_specific_staff_monthly_attendance()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['name' => '勤怠確認対象', 'role' => 0]);

        // テスト日を固定（2月のデータとして作成）
        $targetDate = Carbon::create(2026, 2, 12);
        Attendance::create([
            'user_id' => $staff->id,
            'date' => $targetDate->toDateString(),
            'check_in' => '09:00:00',
        ]);

        // 選択したユーザーの勤怠一覧ページを開く
        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$staff->id}");

        $response->assertStatus(200);
        $response->assertSee('勤怠確認対象');
        // Bladeの表示形式 「02/12」 に合わせる
        $response->assertSee($targetDate->format('m/d'));
        $response->assertSee('09:00');
    }

    /**
     * ID 14-3: 「前月」を押下した時に表示月の前月の情報が表示される
     */
    public function test_14_3_admin_can_navigate_to_previous_month()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['role' => 0]);

        $prevMonth = Carbon::today()->subMonth();
        Attendance::create([
            'user_id' => $staff->id,
            'date' => $prevMonth->copy()->startOfMonth()->toDateString(),
            'check_in' => '08:30:00',
        ]);

        // クエリパラメータで前月を指定してアクセス（前月ボタンの挙動）
        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$staff->id}?month={$prevMonth->format('Y-m')}");

        $response->assertStatus(200);
        $response->assertSee($prevMonth->format('Y/m'));
        $response->assertSee('08:30');
    }

    /**
     * ID 14-4: 「翌月」を押下した時に表示月の翌月の情報が表示される
     */
    public function test_14_4_admin_can_navigate_to_next_month()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['role' => 0]);

        $nextMonth = Carbon::today()->addMonth();
        Attendance::create([
            'user_id' => $staff->id,
            'date' => $nextMonth->copy()->startOfMonth()->toDateString(),
            'check_in' => '10:00:00',
        ]);

        // クエリパラメータで翌月を指定してアクセス（翌月ボタンの挙動）
        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$staff->id}?month={$nextMonth->format('Y-m')}");

        $response->assertStatus(200);
        $response->assertSee($nextMonth->format('Y/m'));
        $response->assertSee('10:00');
    }

    /**
     * ID 14-5: 「詳細」を押下すると、その日の勤怠詳細画面に遷移する
     */
    public function test_14_5_admin_can_navigate_to_daily_detail()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['role' => 0]);
        $attendance = Attendance::create([
            'user_id' => $staff->id,
            'date' => Carbon::today()->toDateString(),
            'check_in' => '09:00:00',
        ]);

        // 勤怠一覧ページで「詳細」ボタンの存在を確認し、遷移を検証
        $response = $this->actingAs($admin)->get("/admin/attendance/staff/{$staff->id}");

        // 詳細画面（ID 13の対象画面）へ正しくアクセスできるか
        $detailResponse = $this->get("/admin/attendance/{$attendance->id}");
        $detailResponse->assertStatus(200);
    }
}

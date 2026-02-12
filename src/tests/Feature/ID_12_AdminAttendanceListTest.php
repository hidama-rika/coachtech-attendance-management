<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class ID_12_AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
    }

    /**
     * ID 12-1: 管理者ユーザーがその日の全スタッフの勤怠情報を閲覧できる
     */
    public function test_12_1_admin_can_view_all_staff_attendance()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff1 = User::factory()->create(['name' => 'スタッフA', 'role' => 0]);
        $staff2 = User::factory()->create(['name' => 'スタッフB', 'role' => 0]);

        $today = Carbon::today()->toDateString();
        Attendance::create(['user_id' => $staff1->id, 'date' => $today, 'check_in' => '09:00:00']);
        Attendance::create(['user_id' => $staff2->id, 'date' => $today, 'check_in' => '10:00:00']);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('スタッフA');
        $response->assertSee('スタッフB');
    }

    /**
     * ID 12-2: 前日ボタンを押下した時に、前の日の勤怠情報が表示される
     */
    public function test_12_2_admin_can_view_previous_day_attendance()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['name' => '昨日働いた人', 'role' => 0]);

        $yesterday = Carbon::yesterday();
        Attendance::create([
            'user_id' => $staff->id,
            'date' => $yesterday->toDateString(),
            'check_in' => '09:00:00'
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/list?date={$yesterday->toDateString()}");

        $response->assertStatus(200);
        // コントローラーの format('Y/m/d') に合わせる
        $response->assertSee($yesterday->format('Y/m/d'));
        $response->assertSee('昨日働いた人');
    }

    /**
     * ID 12-3: 翌日ボタンを押下した時に、次の日の勤怠情報が表示される
     */
    public function test_12_3_admin_can_view_next_day_attendance()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['name' => '明日働く人', 'role' => 0]);

        $tomorrow = Carbon::tomorrow();
        Attendance::create([
            'user_id' => $staff->id,
            'date' => $tomorrow->toDateString(),
            'check_in' => '09:00:00'
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/list?date={$tomorrow->toDateString()}");

        $response->assertStatus(200);
        $response->assertSee($tomorrow->format('Y/m/d'));
        $response->assertSee('明日働く人');
    }

    /**
     * ID 12-4: 日付指定でその日の勤怠情報が表示される
     */
    public function test_12_4_admin_can_view_specific_date_attendance()
    {
        $admin = User::factory()->create(['role' => 1]);
        $staff = User::factory()->create(['name' => '特定日の人', 'role' => 0]);

        $specificDate = Carbon::parse('2026-05-20');
        Attendance::create([
            'user_id' => $staff->id,
            'date' => $specificDate->toDateString(),
            'check_in' => '09:00:00'
        ]);

        $response = $this->actingAs($admin)->get("/admin/attendance/list?date={$specificDate->toDateString()}");

        $response->assertStatus(200);
        $response->assertSee($specificDate->format('Y/m/d'));
        $response->assertSee('特定日の人');
    }
}

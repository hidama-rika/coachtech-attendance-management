<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ID_4_TimeDisplayTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 4: 現在の日時情報がUIと同じ形式で出力されている
     */
    public function test_current_datetime_is_displayed_correctly_on_attendance_page()
    {
        // 1. ログインユーザーを準備（勤怠打刻画面を開くため）
        $user = User::factory()->create();

        // 2. 現在の日時を固定する (テストの期待値と一致させるため)
        // 日本語の曜日を表示させる場合はロケールを設定
        Carbon::setLocale('ja');
        $now = Carbon::create(2026, 2, 11, 14, 0, 0);
        Carbon::setTestNow($now);

        // 3. 勤怠打刻画面を開く
        $response = $this->actingAs($user)->get('/attendance');

        // 4. 画面上に表示されている日時情報を確認する
        $response->assertStatus(200);

        // UIのフォーマットに合わせて調整してください（例: "2026年2月11日(水)"）
        $expectedDate = $now->isoFormat('YYYY年M月D日');
        $expectedDay = '(' . $now->isoFormat('ddd') . ')';

        // 期待値が画面に含まれているか確認
        $response->assertSee($expectedDate);
        $response->assertSee($expectedDay);

        // テスト終了後は時間を元に戻す
        Carbon::setTestNow();
    }
}

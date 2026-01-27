<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. メインテストユーザー（テスト スタッフ）を特定
        $mainTester = User::where('name', 'テスト スタッフ')->first();

        if ($mainTester) {
            // 過去90日分（約3か月）のループ
            for ($i = 0; $i < 90; $i++) {
                $targetDate = Carbon::today()->subDays($i);

                // 土日はデータを作らない（休日の想定）
                if ($targetDate->isWeekend()) {
                    continue;
                }

                // 勤怠本体の作成
                $attendance = Attendance::create([
                    'user_id' => $mainTester->id,
                    'date'    => $targetDate->toDateString(),
                    'check_in'  => '09:00:00',
                    'check_out' => '18:00:00',
                    'remark'    => ($i % 15 == 0) ? '有給休暇の振替' : '', // たまに備考を入れる
                ]);

                // 休憩パターンの作成
                if ($i % 3 == 0) {
                    // 3日に1回は休憩2回（B案：動的追加のテスト用）
                    $attendance->rests()->create(['start_time' => '12:00:00', 'end_time' => '13:00:00']);
                    $attendance->rests()->create(['start_time' => '15:00:00', 'end_time' => '15:15:00']);
                } else {
                    // 通常は休憩1回
                    $attendance->rests()->create(['start_time' => '12:00:00', 'end_time' => '13:00:00']);
                }
            }
        }

        // 2. Figmaデザインのメンバー（西さん含むその他全員）
        // 今日1日分だけ作成（管理者一覧の賑やかし用）
        $others = User::where('role', 0)->where('id', '!=', $mainTester?->id)->get();
        foreach ($others as $user) {
            Attendance::create([
                'user_id' => $user->id,
                'date'    => Carbon::today()->toDateString(),
                'check_in'  => '08:50:00',
                'check_out' => null, // 勤務中として表示
                'remark'    => '',
            ]);
        }
    }
}

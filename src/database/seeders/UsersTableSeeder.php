<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User; // ❗ Userモデルをインポート
use Illuminate\Support\Facades\Hash; // ❗ Hashファサードをインポート

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 管理者ユーザー
        User::create([
            'name' => '管理者 ロール',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 1, // 管理者権限
        ]);

        // 一般ユーザー
        User::create([
            'name' => 'テスト スタッフ',
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'),
            'role' => 0, // 一般スタッフ
        ]);
    }
}

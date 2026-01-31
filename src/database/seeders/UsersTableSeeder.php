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
        // 1. 管理者ユーザー
        User::create([
            'name' => '管理者 ロール',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(), // ★検証済みとして追加★
            'role' => 1, // 管理者権限
        ]);

        // 2. テスト用スタッフ
        User::create([
            'name' => 'テスト スタッフ',
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(), // ★検証済みとして追加★
            'role' => 0, // 一般スタッフ
        ]);

        // 3. Figmaデザインに登場するユーザーを追加
        $figmaUsers = [
            ['name' => '西 伶奈', 'email' => 'reina.n@example.com'],
            ['name' => '山田 太郎', 'email' => 'taro.y@example.com'],
            ['name' => '増田 一世', 'email' => 'issei.m@example.com'],
            ['name' => '山本 敬吉', 'email' => 'keikichi.y@example.com'],
            ['name' => '秋田 朋美', 'email' => 'tomomi.a@example.com'],
            ['name' => '中西 教夫', 'email' => 'norio.n@example.com'],
        ];

        foreach ($figmaUsers as $userData) {
            User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password123'), // 全員共通パスワード
                'email_verified_at' => now(), // ★検証済みとして追加★
                'role' => 0, // 一般スタッフ
            ]);
        }
    }
}

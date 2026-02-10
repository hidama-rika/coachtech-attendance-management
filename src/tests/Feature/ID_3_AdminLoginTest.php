<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ID_3_AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 3-1: メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_email_is_required()
    {
        // 管理者ユーザーを登録する
        $admin = User::factory()->create([
            'role' => 1, // 管理者権限を付与（実装に合わせて変更してください）
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * ID 3-2: パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_admin_password_is_required()
    {
        // 管理者ユーザーを登録する
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 1,
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * ID 3-3: 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_admin_login_fails_with_invalid_credentials()
    {
        // 管理者ユーザーを登録する
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 1,
            'password' => bcrypt('password123'),
        ]);

        // 誤ったメールアドレスでログインを試行
        $response = $this->post('/login', [
            'email' => 'wrong-admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}

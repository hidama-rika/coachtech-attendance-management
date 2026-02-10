<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ID_2_StaffLoginTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 2-1: メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required()
    {
        // ユーザーを登録する
        $user = User::factory()->create([
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
     * ID 2-2: パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required()
    {
        // ユーザーを登録する
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * ID 2-3: 登録内容と一致しない場合、バリデーションメッセージが表示される
     */
    public function test_login_fails_with_invalid_credentials()
    {
        // ユーザーを登録する
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 誤ったメールアドレスでログインを試行
        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}

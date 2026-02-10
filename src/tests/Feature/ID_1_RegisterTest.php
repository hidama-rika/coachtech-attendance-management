<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class ID_1_RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 1-1: 名前が未入力の場合、バリデーションメッセージが表示される
     */
    public function test_name_is_required()
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(302); // バリデーションエラーでリダイレクトされる
        $response->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    /**
     * ID 1-2: メールアドレスが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_email_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    /**
     * ID 1-3: パスワードが8文字未満の場合、バリデーションメッセージが表示される
     */
    public function test_password_length_is_insufficient()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    /**
     * ID 1-4: パスワードが一致しない場合、バリデーションメッセージが表示される
     */
    public function test_password_confirmation_does_not_match()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    /**
     * ID 1-5: パスワードが未入力の場合、バリデーションメッセージが表示される
     */
    public function test_password_is_required()
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    /**
     * ID 1-6: 正しく入力されていた場合、データが正常に保存される
     */
    public function test_registration_is_successful()
    {
        $userData = [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        // データベースに保存されているか確認
        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 登録後の遷移先などを必要に応じて追加（例：メール認証画面へのリダイレクトなど）
    }
}

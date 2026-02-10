<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ID_16_MailVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ID 16-1: 会員登録後、認証メールが送信される
     */
    public function test_verification_email_is_sent_after_registration()
    {
        Notification::fake();

        // 会員登録の処理を実行
        $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // 登録したメールアドレス宛に認証メールが送信されていることを確認
        $user = User::where('email', 'test@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * ID 16-2: メール認証誘導画面で「認証はこちらから」ボタンを押下するとメール認証サイトに遷移する
     * ※Laravel標準のVerifyEmail通知URLの生成と遷移をテストします
     */
    public function test_can_access_verification_url_from_email()
    {
        $user = User::factory()->unverified()->create();

        // 認証用の署名付きURLを手動生成（「認証はこちらから」ボタンのリンク先に相当）
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // URLにアクセス（ボタン押下）
        $response = $this->actingAs($user)->get($verificationUrl);

        // メール認証サイト（検証処理）へ正常にアクセスできることを確認
        $response->assertStatus(302);
    }

    /**
     * ID 16-3: メール認証を完了すると、勤怠登録画面に遷移する
     */
    public function test_redirects_to_attendance_page_after_verification()
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // 認証を完了させる
        $response = $this->actingAs($user)->get($verificationUrl);

        // 勤怠登録画面（トップページ等）に遷移することを確認
        $response->assertRedirect('/attendance?verified=1');

        // ユーザーの認証日時が更新されているか確認
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}

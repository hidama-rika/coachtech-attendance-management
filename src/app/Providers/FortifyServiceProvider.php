<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // ❗ Authの追加を忘れずに
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;
// ❗ 以下の2つを正しく使うことがポイント
use App\Http\Responses\LoginResponse; // ❗ 作成した外部クラスを読み込む
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ❗ Fortifyの標準レスポンスを、作成した自作クラスに置き換える設定
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        // 一般ユーザー用の登録ビュー (US001)
        Fortify::registerView(function () {
            return view('auth.register');
        });

        // 一般ユーザー用のログインビュー (US002)
        Fortify::loginView(function () {
            return view('auth.login');
        });

        // ログイン試行の回数制限設定
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            return Limit::perMinute(10)->by($email . $request->ip());
        });

    }
}

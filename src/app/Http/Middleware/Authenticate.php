<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * 未認証の場合のリダイレクト先を指定 (FN014, FN015)
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            // URLが 'admin' で始まっている場合は管理者ログイン画面へ
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login'); // admin用のルート名を指定
            }

            // それ以外は一般ユーザー用のログイン画面へ
            return route('login');
        }
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // ❗ これを必ず追加

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        // 1. 未ログインなら、管理者ログイン画面へ
        if (!Auth::check()) {
            return redirect('/admin/login');
        }

        // 2. ログインしているが一般ユーザー（role: 0）の場合
        if (Auth::user()->role !== 1) {
            // 一旦ログアウトさせてから、一般用ログイン画面へ飛ばす
            Auth::logout();
            return redirect('/login')->with('error', 'スタッフの方は、こちらから再度ログインしてください。');
        }

    return $next($request);
    }
}

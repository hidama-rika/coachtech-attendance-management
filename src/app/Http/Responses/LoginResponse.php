<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // ログイン中のユーザーを取得
        $user = Auth::user();

        // ロール（役職）を判定
        // 1: 管理者, 0: 一般ユーザー
        if ($user->role === 1) {
            return redirect()->intended('/admin/attendance/list'); // 管理者用画面
        }

        return redirect()->intended('/attendance'); // 一般ユーザー用画面
    }
}
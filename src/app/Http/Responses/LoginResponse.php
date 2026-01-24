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

        // 管理者画面(/admin/login)からログインしたが一般スタッフだった場合
        if ($request->is('admin/login') && $user->role === 0) {
            Auth::logout();
            return redirect('/login')->with('error', 'スタッフの方は、こちらから再度ログインしてください。');
        }

        // 正常なリダイレクト先 (US004, US005)
        // 管理者(role:1)なら管理者一覧へ、スタッフ(role:0)なら打刻画面へ
        $redirect = ($user->role === 1) ? '/admin/attendance/list' : '/attendance';

        return redirect()->intended($redirect);
    }
}
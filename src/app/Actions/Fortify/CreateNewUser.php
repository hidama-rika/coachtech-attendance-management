<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            //Fortifyを使っている場合、会員登録に関しては RegisterRequest を使わない。
            // その代わりに、CreateNewUser.php がバリデーションとユーザー作成の両方を一手に引き受けている
            //会員登録については RegisterRequest.php を別途作成していても、Fortifyを通る限りそれは使われません。CreateNewUser.php の中の Validator::make がその役割を果たしている

            // 名前：必須、文字列、最大50文字
            'name' => ['required', 'string', 'max:50'],

            // メールアドレス：必須、文字列、メール形式、最大255文字、usersテーブルで唯一
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],

            // パスワード：必須、文字列、8文字以上、確認用パスワードと一致
            // $this->passwordRules() 内で 'confirmed' が含まれています
            'password' => $this->passwordRules(),

            // 確認用パスワード：messagesで個別に制御するために明示的に追加
            'password_confirmation' => ['required', 'string', 'min:8'],
        ], [
            // --- カスタムエラーメッセージ (仕様書 FN003 準拠) ---

            // name (仕様書 FN003 - 1. お名前を入力してください)
            'name.required' => 'お名前を入力してください',

            // email (仕様書 FN003 - 2. メールアドレスを入力してください)
            'email.required' => 'メールアドレスを入力してください',

            // password (仕様書 FN003 - 3. パスワードを入力してください / 2.1. パスワードは8文字以上で入力してください)
            'password.required' => 'パスワードを入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',

            // password_confirmation (messagesでpassword_confirmationを参照するには、rulesに明記が必要)
            'password_confirmation.required' => 'パスワードを入力してください', // FN003 3.と合わせて「未入力」のメッセージを統一
            'password_confirmation.min' => 'パスワードは8文字以上で入力してください',

            // password.confirmed (仕様書 FN003 - 3.1. パスワードと一致しません)
            'password.confirmed' => 'パスワードと一致しません',
        ])->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            // roleはマイグレーションのdefault(0)で一般ユーザーになります
        ]);
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; //Rule::in などの Rule クラスを使った記述があるファイルにのみ追記が必要

class ApproveRequest extends FormRequest
{
    /**
     * 権限を許可（管理者認証はMiddleware側で行われるため）
     */
    public function authorize()
    {
        return true;
    }

    /**
     * 今回はURLパラメータ($requestId)で処理を行うため、
     * フォーム入力値のバリデーションは不要
     */
    public function rules()
    {
        return [];
    }
}
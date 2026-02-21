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

    public function rules()
    {
        return [
            // 基本設計書の「入力必須、存在する申請IDであること」を実装
            'attendance_correct_request_id' => ['required', 'exists:attendance_correct_requests,id'],
        ];
    }

    /**
    * URLパラメータ（パス変数）をバリデーション対象にマージする
    */
    public function validationData()
    {
        return array_merge($this->all(), [
            'attendance_correct_request_id' => $this->route('attendance_correct_request_id'),
        ]);
    }
}
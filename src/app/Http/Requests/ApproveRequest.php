<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; //Rule::in などの Rule クラスを使った記述があるファイルにのみ追記が必要

class ApproveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // statusは「承認」か「却下」のいずれかであることを保証
            // これにより、FN051の「承認」処理へ安全に橋渡しできる
            'status' => [
                'required',
                Rule::in([
                    1, // 承認
                    2  // 却下
                ])
            ],
        ];
    }

    public function messages()
    {
        return [
            'status.required' => '承認または却下を選択してください。',
            'status.in'       => '不適切な操作が行われました。',
        ];
    }
}

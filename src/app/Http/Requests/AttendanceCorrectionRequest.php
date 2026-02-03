<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'check_in'  => ['required', 'date_format:H:i'],
            'check_out' => ['required', 'date_format:H:i', 'after:check_in'],
            'remark'    => ['required', 'string'],

            // 休憩時間のバリデーションを追加統合
            'rests.*.start_time' => [
                'required',
                'date_format:H:i',
                'after_or_equal:check_in',  // 出勤時間以降
                'before_or_equal:check_out' // 退勤時間以前
            ],
            'rests.*.end_time' => [
                'required',
                'date_format:H:i',
                'after:rests.*.start_time', // 開始時間より後
                'before_or_equal:check_out' // 退勤時間以前
            ],
        ];
    }

    public function attributes()
    {
        return [
            'check_in'  => '出勤時間',
            'check_out' => '退勤時間',
            'remark'    => '備考',
            'rests.*.start_time' => '休憩開始時間',
            'rests.*.end_time'   => '休憩終了時間',
        ];
    }

    public function messages()
{
    return [
        // FN029-1: 出退勤の不備
        'check_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
        'check_in.required' => '出勤時間を入力してください',
        'check_out.required' => '退勤時間を入力してください',

        // FN029-2, 3: 休憩時間の不備（ワイルドカード部分も全て指定）
        'rests.*.start_time.required' => '休憩時間が不適切な値です', // FN029-2の文言を流用
        'rests.*.start_time.after_or_equal' => '休憩時間が不適切な値です',
        'rests.*.start_time.before_or_equal' => '休憩時間が不適切な値です',

        'rests.*.end_time.required' => '休憩時間もしくは退勤時間が不適切な値です', // FN029-3の文言を流用
        'rests.*.end_time.after' => '休憩時間もしくは退勤時間が不適切な値です',
        'rests.*.end_time.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',

        // FN029-4: 備考
        'remark.required' => '備考を記入してください',
    ];
}
}

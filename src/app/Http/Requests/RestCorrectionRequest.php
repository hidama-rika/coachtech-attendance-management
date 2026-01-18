<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RestCorrectionRequest extends FormRequest
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
            'rests.*.start_time' => [
                'required',
                'date_format:H:i',
                'after_or_equal:check_in', // 出勤時間以降であること
                'before_or_equal:check_out' // 退勤時間以前であること
            ],
            'rests.*.end_time' => [
                'required',
                'date_format:H:i',
                // afterルールは、同じ配列内のstart_timeと比較される
                'after:rests.*.start_time', // 開始より後であること
                'before_or_equal:check_out' // 退勤時間以前であること
            ],
        ];
    }

    public function messages()
    {
        return [
            // FN029-2
            'rests.*.start_time.after_or_equal' => '休憩時間が不適切な値です',
            'rests.*.start_time.before_or_equal' => '休憩時間が不適切な値です',

            // FN029-3
            'rests.*.end_time.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'rests.*.end_time.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
        ];
    }
}

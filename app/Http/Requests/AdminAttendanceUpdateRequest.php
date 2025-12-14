<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
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
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],

            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i', 'after:break_start'],

            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages() {
        return [
            'clock_in.required' => '出勤時刻を入力してください',
            'clock_out.required' => '退勤時刻を入力してください',
            'clock_out.after' => '退勤時刻は出勤時刻より後の時間にしてください',

            'break_end.after' => '休憩終了は休憩開始より後の時間にしてください',
        ];
    }
}

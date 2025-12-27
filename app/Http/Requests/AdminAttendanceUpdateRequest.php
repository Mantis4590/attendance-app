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
            'clock_in'  => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],

            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end'   => ['nullable', 'date_format:H:i'],

            'note' => ['required'],
        ];
    }

    public function messages() {
        return [
            'note.required' => '備考を記入してください'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn  = $this->clock_in;
            $clockOut = $this->clock_out;

            // 出勤・退勤
            if ($clockIn && $clockOut && $clockIn >= $clockOut) {
                $validator->errors()->add(
                    'clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            // 休憩
            foreach ($this->breaks ?? [] as $break) {
                $start = $break['start'] ?? null;
                $end   = $break['end'] ?? null;

                if ($start && ($start < $clockIn || $start > $clockOut)) {
                    $validator->errors()->add(
                        'breaks',
                        '休憩時間が不適切な値です'
                    );
                }

                if ($end && $end > $clockOut) {
                    $validator->errors()->add(
                        'breaks',
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }

                if ($start && $end && $start >= $end) {
                    $validator->errors()->add(
                        'breaks',
                        '休憩時間が不適切な値です'
                    );
                }
            }
        });
    }
    
}

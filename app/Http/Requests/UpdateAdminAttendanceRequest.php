<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminAttendanceRequest extends FormRequest
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
            'clock_in' => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i'];

            'breaks' => ['array'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'break.*.end' => ['nullable', 'date_format:H:i'],

            'note' => ['required'],
        ];
    }

    public function messages(): array {
        return [
            'clock_in.date_format' => '出勤時間もしくは退勤時間が不適切な値です'
            'clock_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です',

            'breaks.*.start.date_format' => '休憩時間が不適切な値です',
            'breaks.*.end.date_format' => '休憩時間が不適切な値です',

            'note.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator) {
        $validator->after(function ($validator) {
            if (!$this->clock_in || !$this->clock_out) {
                return;
            }

            $clockIn = Carbon::createFromFormat('H:i', $this->clock_in);
            $clockOut = Carbon::createFromFormat('H:i', $this->clock_out);

            /** 出勤・退勤 */
            if ($clockIn->gte($clockOut)) {
                $validator->errors()->add(
                    'clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            /** 休憩 */
            foreach ($this->breaks ?? [] as $break) {
                if (empty($break['start']) || empty($break['end'])) {
                    continue;
                };

                $breakStart = Carbon::createFromFormat('H:i', $break['start']);
                $breakEnd = Carbon::createFromFormat('H:i', $break['end']);

                if (
                    $breakStart->lt($clockIn) ||
                    $breakStart->gt($clockOut) ||
                    $breakEnd->gt($clockOut)
                ) {
                    $validator->errors()->add(
                        'breaks',
                        '休憩時間が不適切な値です'
                    );
                }
            }
        });
    }
}

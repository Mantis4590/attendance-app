<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AttendanceUpdateRequest extends FormRequest
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
            'clock_out' => ['required', 'date_format:H:i'],
            'note' => ['required'],

            // 休憩
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
        ];
    }

    public function messages() {
        return [
            'clock_in.required' => '出勤時間が未入力です',
            'clock_out.required' => '退勤時間が未入力です',
            'clock_in.date_format' => '出勤時間形式が不正です',
            'clock_out.date_format' => '退勤時間形式が不正です',
            'note.required' => '備考を記入してください',

            'breaks.*.start.date_format' => '休憩時間が不適切な値です',
            'breaks.*.end.date_format' => '休憩時間もしくは退勤時間が不適切な値です',
        ];
    }

    public function withValidator ($validator) {
        $validator->after(function ($validator) {
            // 出退勤
            $in = Carbon::createFromFormat('H:i', $this->clock_in);
            $out = Carbon::createFromFormat('H:i', $this->clock_out);

            if ($out->lessThanOrEqualTo($in)) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩チェック
            if ($this->breaks) {
                foreach ($this->breaks as $index => $break) {
                    if (!empty($break['start']) && !empty($break['end'])) {
                        $start = Carbon::createFromFormat('H:i', $break['start']);
                        $end = Carbon::createFromFormat('H:i', $break['end']);

                        if ($end->lessThanOrEqualTo($start)) {
                            $validator->errors()->add("breaks.$index.end", '休憩時間が不適切な値です');
                        }

                        // 出勤前 or 退勤後に休憩開始
                        if ($start->lessThan($in) || $start->greaterThan($out)) {
                            $validator->errors()->add(
                                    "breaks.$index.start",
                                    '休憩時間が不適切な値です'
                            );
                        }

                       // 退勤後に休憩終了はアウト
                        if ($end->greaterThan($out)) {
                            $validator->errors()->add(
                                    "breaks.$index.end",
                                    '休憩時間もしくは退勤時間が不適切な値です'
                            );
                        }
                    }
                }
            }
        });
    }
}

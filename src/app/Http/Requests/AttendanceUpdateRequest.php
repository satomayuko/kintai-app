<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],

            'break1_start' => ['nullable', 'date_format:H:i'],
            'break1_end' => ['nullable', 'date_format:H:i'],
            'break2_start' => ['nullable', 'date_format:H:i'],
            'break2_end' => ['nullable', 'date_format:H:i'],

            'remark' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $start = $this->input('start_time');
            $end = $this->input('end_time');

            if ($start && $end && $start >= $end) {
                $validator->errors()->add('time_range', '出勤時間もしくは退勤時間が不適切な値です。');
                return;
            }

            if (!($start && $end)) {
                return;
            }

            $breaks = [
                [$this->input('break1_start'), $this->input('break1_end')],
                [$this->input('break2_start'), $this->input('break2_end')],
            ];

            foreach ($breaks as [$bs, $be]) {
                if (!$bs && !$be) {
                    continue;
                }

                if (!$bs || !$be) {
                    $validator->errors()->add('break_range', '休憩時間が勤務時間外です。');
                    return;
                }

                if ($bs < $start) {
                    $validator->errors()->add('break_range', '休憩時間が不適切な値です。');
                    return;
                }

                if ($bs > $end) {
                    $validator->errors()->add('break_range', '休憩時間が不適切な値です。');
                    return;
                }

                if ($be > $end) {
                    $validator->errors()->add('time_range', '休憩時間もしくは退勤時間が不適切な値です。');
                    return;
                }

                if ($be < $start) {
                    $validator->errors()->add('break_range', '休憩時間が勤務時間外です。');
                    return;
                }

                if ($bs >= $be) {
                    $validator->errors()->add('break_range', '休憩時間が勤務時間外です。');
                    return;
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'remark.required' => '備考を記入してください。',
        ];
    }
}
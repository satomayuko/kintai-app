<?php

namespace App\Http\Requests;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StampCorrectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_id' => [
                'required',
                'integer',
                Rule::exists('attendances', 'id')->where(fn ($q) => $q->where('user_id', Auth::id())),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time'   => ['required', 'date_format:H:i'],

            'breaks' => ['nullable', 'array'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end'   => ['nullable', 'date_format:H:i'],

            'remark' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $v) {
            $attendanceId = $this->input('attendance_id');
            $attendance = $attendanceId ? Attendance::query()->find($attendanceId) : null;
            if (!$attendance) {
                return;
            }

            $workDate = Carbon::parse($attendance->work_date)->format('Y-m-d');

            $startStr = $this->input('start_time');
            $endStr   = $this->input('end_time');

            if (!$startStr || !$endStr) {
                return;
            }

            $start = Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $startStr);
            $end   = Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $endStr);

            if ($start->gte($end)) {
                $v->errors()->add('time_range', '出勤時間もしくは退勤時間が不適切な値です');
                return;
            }

            $breaks = $this->input('breaks', []);
            if (!is_array($breaks)) {
                return;
            }

            foreach ($breaks as $row) {
                $bsStr = $row['break_start'] ?? null;
                $beStr = $row['break_end'] ?? null;

                $bsStr = ($bsStr === '') ? null : $bsStr;
                $beStr = ($beStr === '') ? null : $beStr;

                if ($bsStr === null && $beStr === null) {
                    continue;
                }

                if ($bsStr === null || $beStr === null) {
                    $v->errors()->add('break_range', '休憩時間が不適切な値です');
                    return;
                }

                $bs = Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $bsStr);
                $be = Carbon::createFromFormat('Y-m-d H:i', $workDate . ' ' . $beStr);

                if ($bs->lt($start) || $bs->gt($end)) {
                    $v->errors()->add('break_range', '休憩時間が不適切な値です');
                    return;
                }

                if ($be->gt($end)) {
                    $v->errors()->add('time_range', '休憩時間もしくは退勤時間が不適切な値です');
                    return;
                }

                if ($be->lte($bs)) {
                    $v->errors()->add('break_range', '休憩時間が不適切な値です');
                    return;
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'remark.required' => '備考を記入してください',
        ];
    }
}
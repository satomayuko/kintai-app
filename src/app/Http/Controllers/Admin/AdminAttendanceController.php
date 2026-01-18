<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{
    public function daily(Request $request): View
    {
        return $this->list($request);
    }

    public function list(Request $request): View
    {
        $monthParam = $request->query('month');

        if ($monthParam) {
            try {
                $currentMonth = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
            } catch (\Exception $e) {
                $currentMonth = Carbon::today()->startOfMonth();
            }
        } else {
            $currentMonth = Carbon::today()->startOfMonth();
        }

        $prevMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();

        $startDate = $currentMonth->copy()->startOfMonth();
        $endDate   = $currentMonth->copy()->endOfMonth();

        $users = User::query()->orderBy('id')->get();

        $selectedUserId = (int) $request->query('user_id', 0);
        if ($selectedUserId === 0 && $users->isNotEmpty()) {
            $selectedUserId = (int) $users->first()->id;
        }

        $attendances = Attendance::query()
            ->where('user_id', $selectedUserId)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->orderBy('work_date')
            ->get();

        $attendanceIds = $attendances->pluck('id');

        $breakSecondsByAttendance = DB::table('breaks')
            ->select('attendance_id', DB::raw('SUM(TIMESTAMPDIFF(SECOND, break_start, break_end)) as break_seconds'))
            ->whereIn('attendance_id', $attendanceIds)
            ->whereNotNull('break_end')
            ->groupBy('attendance_id')
            ->pluck('break_seconds', 'attendance_id');

        $attendances->each(function ($attendance) use ($breakSecondsByAttendance) {
            $breakSeconds = (int) $breakSecondsByAttendance->get($attendance->id, 0);
            $breakMinutes = $breakSeconds > 0 ? (int) ceil($breakSeconds / 60) : 0;

            if ($breakMinutes > 0) {
                $breakHours = intdiv($breakMinutes, 60);
                $breakRemainMinutes = $breakMinutes % 60;
                $attendance->break_time_display = sprintf('%d:%02d', $breakHours, $breakRemainMinutes);
            } else {
                $attendance->break_time_display = '';
            }

            if ($attendance->start_time && $attendance->end_time) {
                $workSeconds = Carbon::parse($attendance->end_time)->diffInSeconds(Carbon::parse($attendance->start_time));
                $workMinutes = (int) floor($workSeconds / 60) - $breakMinutes;
                if ($workMinutes < 0) {
                    $workMinutes = 0;
                }

                if ($workMinutes > 0) {
                    $workHours = intdiv($workMinutes, 60);
                    $workRemainMinutes = $workMinutes % 60;
                    $attendance->work_time_display = sprintf('%d:%02d', $workHours, $workRemainMinutes);
                } else {
                    $attendance->work_time_display = '';
                }
            } else {
                $attendance->work_time_display = '';
            }
        });

        return view('admin.attendance.list', [
            'users'          => $users,
            'selectedUserId' => $selectedUserId,
            'attendances'    => $attendances,

            'currentMonth'   => $currentMonth,
            'prevMonth'      => $prevMonth,
            'nextMonth'      => $nextMonth,

            'currentDate'    => $currentMonth,
            'prevDate'       => $prevMonth,
            'nextDate'       => $nextMonth,
        ]);
    }

    public function monthly(Request $request, int $id): View
    {
        $staff = User::query()->where('id', $id)->firstOrFail();

        $monthParam = $request->query('month');

        if ($monthParam) {
            try {
                $month = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
            } catch (\Exception $e) {
                $month = Carbon::today()->startOfMonth();
            }
        } else {
            $month = Carbon::today()->startOfMonth();
        }

        $startDate = $month->copy()->startOfMonth();
        $endDate   = $month->copy()->endOfMonth();

        $attendances = Attendance::query()
            ->where('user_id', $staff->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->with(['breaks'])
            ->orderBy('work_date')
            ->get();

        return view('admin.attendance.staff', [
            'staff'       => $staff,
            'attendances' => $attendances,
        ]);
    }

    public function exportCsv(Request $request, int $id): StreamedResponse
    {
        $staff = User::query()->where('id', $id)->firstOrFail();

        $monthParam = $request->query('month');

        if ($monthParam) {
            try {
                $month = Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
            } catch (\Exception $e) {
                $month = Carbon::today()->startOfMonth();
            }
        } else {
            $month = Carbon::today()->startOfMonth();
        }

        $startDate = $month->copy()->startOfMonth();
        $endDate   = $month->copy()->endOfMonth();

        $attendances = Attendance::query()
            ->where('user_id', $staff->id)
            ->whereBetween('work_date', [$startDate, $endDate])
            ->with(['breaks'])
            ->orderBy('work_date')
            ->get();

        $filename = sprintf('attendance_%s_%s.csv', $staff->id, $month->format('Y-m'));

        return response()->streamDownload(function () use ($attendances) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($out, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($attendances as $a) {
                $dateKey = $a->work_date ? Carbon::parse($a->work_date)->toDateString() : null;

                $date  = $a->work_date ? Carbon::parse($a->work_date)->format('Y-m-d') : '';
                $start = $a->start_time ? Carbon::parse($a->start_time)->format('H:i') : '';
                $end   = $a->end_time ? Carbon::parse($a->end_time)->format('H:i') : '';

                $breakMinutes = null;
                $workMinutes  = null;

                if ($dateKey && $a->start_time && $a->end_time) {
                    $breakMinutes = 0;
                    foreach (($a->breaks ?? collect()) as $b) {
                        $bs = $b->break_start ?? null;
                        $be = $b->break_end ?? null;
                        if ($bs && $be) {
                            $bsC = Carbon::parse($dateKey . ' ' . Carbon::parse($bs)->format('H:i:s'));
                            $beC = Carbon::parse($dateKey . ' ' . Carbon::parse($be)->format('H:i:s'));
                            $breakMinutes += $bsC->diffInMinutes($beC);
                        }
                    }

                    $st = Carbon::parse($dateKey . ' ' . Carbon::parse($a->start_time)->format('H:i:s'));
                    $en = Carbon::parse($dateKey . ' ' . Carbon::parse($a->end_time)->format('H:i:s'));
                    $total = $st->diffInMinutes($en);
                    $workMinutes = max(0, $total - ($breakMinutes ?? 0));
                }

                $toHM = function (?int $minutes): string {
                    if ($minutes === null) {
                        return '';
                    }
                    $h = intdiv($minutes, 60);
                    $m = $minutes % 60;
                    return $h . ':' . str_pad((string) $m, 2, '0', STR_PAD_LEFT);
                };

                fputcsv($out, [
                    $date,
                    $start,
                    $end,
                    $toHM($breakMinutes),
                    $toHM($workMinutes),
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function detail(int $id): View
    {
        $attendance = Attendance::query()->where('id', $id)->firstOrFail();
        $user = User::query()->where('id', $attendance->user_id)->firstOrFail();

        $weeks = ['日', '月', '火', '水', '木', '金', '土'];
        $date  = Carbon::parse($attendance->work_date);
        $week  = $weeks[$date->dayOfWeek];

        $breaks = DB::table('breaks')
            ->where('attendance_id', $attendance->id)
            ->orderBy('break_start')
            ->get();

        $latestRequest = StampCorrectionRequest::query()
            ->with(['breaks'])
            ->where('attendance_id', $attendance->id)
            ->latest('created_at')
            ->first();

        return view('admin.attendance.detail', [
            'attendance'    => $attendance,
            'user'          => $user,
            'date'          => $date,
            'week'          => $week,
            'breaks'        => $breaks,
            'latestRequest' => $latestRequest,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $attendance = Attendance::query()->where('id', $id)->firstOrFail();

        $validated = $request->validate([
            'start_time'   => ['required', 'date_format:H:i'],
            'end_time'     => ['required', 'date_format:H:i'],
            'break1_start' => ['nullable', 'date_format:H:i'],
            'break1_end'   => ['nullable', 'date_format:H:i'],
            'break2_start' => ['nullable', 'date_format:H:i'],
            'break2_end'   => ['nullable', 'date_format:H:i'],
            'remark'       => ['required', 'string', 'max:255'],
        ]);

        $workDate = Carbon::parse($attendance->work_date)->format('Y-m-d');

        $toDateTime = function (?string $hhmm) use ($workDate): ?string {
            if ($hhmm === null || $hhmm === '') {
                return null;
            }
            return Carbon::parse($workDate . ' ' . $hhmm)->format('Y-m-d H:i:s');
        };

        $attendance->start_time = Carbon::createFromFormat('H:i', $validated['start_time'])->format('H:i:s');
        $attendance->end_time   = Carbon::createFromFormat('H:i', $validated['end_time'])->format('H:i:s');
        $attendance->remark     = $validated['remark'];
        $attendance->save();

        $existing = DB::table('breaks')
            ->where('attendance_id', $attendance->id)
            ->orderBy('break_start')
            ->get()
            ->values();

        $break1 = $existing->get(0);
        $break2 = $existing->get(1);

        $b1s = $validated['break1_start'] ?? null;
        $b1e = $validated['break1_end'] ?? null;

        if (($b1s !== null && $b1s !== '') || ($b1e !== null && $b1e !== '')) {
            $payload = [
                'break_start' => $toDateTime($b1s),
                'break_end'   => $toDateTime($b1e),
                'updated_at'  => now(),
            ];

            if ($break1) {
                DB::table('breaks')->where('id', $break1->id)->update($payload);
            } else {
                DB::table('breaks')->insert($payload + [
                    'attendance_id' => $attendance->id,
                    'created_at'    => now(),
                ]);
            }
        } elseif ($break1) {
            DB::table('breaks')->where('id', $break1->id)->delete();
        }

        $b2s = $validated['break2_start'] ?? null;
        $b2e = $validated['break2_end'] ?? null;

        if (($b2s !== null && $b2s !== '') || ($b2e !== null && $b2e !== '')) {
            $payload = [
                'break_start' => $toDateTime($b2s),
                'break_end'   => $toDateTime($b2e),
                'updated_at'  => now(),
            ];

            if ($break2) {
                DB::table('breaks')->where('id', $break2->id)->update($payload);
            } else {
                DB::table('breaks')->insert($payload + [
                    'attendance_id' => $attendance->id,
                    'created_at'    => now(),
                ]);
            }
        } elseif ($break2) {
            DB::table('breaks')->where('id', $break2->id)->delete();
        }

        return redirect()->route('admin.attendance.detail', ['id' => $attendance->id]);
    }
}
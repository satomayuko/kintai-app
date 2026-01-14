<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceUpdateRequest;
use App\Models\Attendance;
use App\Models\User;
use App\Models\WorkBreak;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAttendanceController extends Controller
{
    public function daily(Request $request): View
    {
        $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $currentDate = $request->filled('date')
            ? Carbon::createFromFormat('Y-m-d', $request->input('date'))->startOfDay()
            : Carbon::today()->startOfDay();

        $prevDate = $currentDate->copy()->subDay();
        $nextDate = $currentDate->copy()->addDay();

        $attendances = Attendance::with(['user', 'breaks'])
            ->whereDate('work_date', $currentDate->toDateString())
            ->orderBy('user_id')
            ->get();

        return view('admin.attendance.list', [
            'currentDate' => $currentDate,
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'attendances' => $attendances,
        ]);
    }

    public function monthly(Request $request, int $id): View
    {
        $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $staff = User::query()->findOrFail($id);

        $month = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth()
            : Carbon::today()->startOfMonth();

        $prevMonth = $month->copy()->subMonth()->format('Y-m');
        $nextMonth = $month->copy()->addMonth()->format('Y-m');

        $start = $month->copy()->startOfMonth()->toDateString();
        $end = $month->copy()->endOfMonth()->toDateString();

        $attendances = Attendance::with(['user', 'breaks'])
            ->where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();

        return view('admin.attendance.staff', [
            'staff' => $staff,
            'userId' => $id,
            'month' => $month,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'attendances' => $attendances,
        ]);
    }

    public function detail(int $id): View
    {
        $attendance = Attendance::with(['user', 'breaks', 'correctionRequests'])->findOrFail($id);

        $latestRequest = $attendance->correctionRequests()
            ->orderByDesc('created_at')
            ->first();

        $isPending = $latestRequest && ($latestRequest->status ?? null) === '承認待ち';

        return view('admin.attendance.detail', [
            'attendance' => $attendance,
            'latestRequest' => $latestRequest,
            'isPending' => $isPending,
        ]);
    }

    public function update(AttendanceUpdateRequest $request, int $id): RedirectResponse
    {
        $attendance = Attendance::with(['breaks', 'correctionRequests'])->findOrFail($id);

        $hasPending = $attendance->correctionRequests()
            ->where('status', '承認待ち')
            ->exists();

        if ($hasPending) {
            return back()->withErrors([
                'pending' => '承認待ちのため修正はできません。',
            ])->withInput();
        }

        $validated = $request->validated();

        $dateStr = $attendance->work_date instanceof Carbon
            ? $attendance->work_date->toDateString()
            : Carbon::parse($attendance->work_date)->toDateString();

        $toDatetime = function (?string $time) use ($dateStr) {
            if (!$time) {
                return null;
            }
            return Carbon::createFromFormat('Y-m-d H:i', $dateStr . ' ' . $time);
        };

        $attendance->start_time = $toDatetime($validated['start_time'] ?? null);
        $attendance->end_time = $toDatetime($validated['end_time'] ?? null);
        $attendance->remark = $validated['remark'];
        $attendance->save();

        $breaks = $attendance->breaks()->orderBy('id')->get();

        $applyBreak = function (int $index, ?string $s, ?string $e) use ($attendance, $breaks, $toDatetime) {
            $start = $toDatetime($s);
            $end = $toDatetime($e);

            $model = $breaks->get($index);

            if (!$start && !$end) {
                if ($model) {
                    $model->delete();
                }
                return;
            }

            if (!$model) {
                $model = new WorkBreak();
                $model->attendance_id = $attendance->id;
            }

            $model->break_start = $start;
            $model->break_end = $end;
            $model->save();
        };

        $applyBreak(0, $validated['break1_start'] ?? null, $validated['break1_end'] ?? null);
        $applyBreak(1, $validated['break2_start'] ?? null, $validated['break2_end'] ?? null);

        return redirect()->route('admin.attendance.detail', ['id' => $attendance->id]);
    }

    public function exportCsv(Request $request, int $id): StreamedResponse
    {
        $request->validate([
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $user = User::query()->findOrFail($id);

        $month = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth()
            : Carbon::today()->startOfMonth();

        $start = $month->copy()->startOfMonth()->toDateString();
        $end = $month->copy()->endOfMonth()->toDateString();

        $attendances = Attendance::with(['breaks'])
            ->where('user_id', $id)
            ->whereBetween('work_date', [$start, $end])
            ->orderBy('work_date')
            ->get();

        $fileName = 'attendance_' . $user->id . '_' . $month->format('Y_m') . '.csv';

        return response()->streamDownload(function () use ($attendances) {
            $out = fopen('php://output', 'w');

            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['日付', '出勤', '退勤', '休憩', '合計']);

            $fmtTime = fn ($t) => $t ? Carbon::parse($t)->format('H:i') : '';
            $fmtMinutes = function ($m) {
                if ($m === null) {
                    return '';
                }
                $h = intdiv($m, 60);
                $min = $m % 60;
                return sprintf('%d:%02d', $h, $min);
            };

            foreach ($attendances as $attendance) {
                $breakMinutes = $attendance->breaks->sum(function ($b) {
                    $start = $b->break_start ?? null;
                    $end = $b->break_end ?? null;

                    if (!$start || !$end) {
                        return 0;
                    }

                    return Carbon::parse($start)->diffInMinutes(Carbon::parse($end));
                });

                $workMinutes = null;
                if ($attendance->start_time && $attendance->end_time) {
                    $workMinutes = Carbon::parse($attendance->start_time)->diffInMinutes(Carbon::parse($attendance->end_time));
                }

                $totalMinutes = $workMinutes === null ? null : max($workMinutes - $breakMinutes, 0);

                fputcsv($out, [
                    Carbon::parse($attendance->work_date)->format('m/d'),
                    $fmtTime($attendance->start_time),
                    $fmtTime($attendance->end_time),
                    $workMinutes === null ? '' : $fmtMinutes($breakMinutes),
                    $fmtMinutes($totalMinutes),
                ]);
            }

            fclose($out);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index()
    {
        $user  = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        $status      = '勤務外';
        $canStart    = false;
        $canEnd      = false;
        $canBreakIn  = false;
        $canBreakOut = false;
        $finished    = false;

        if (! $attendance) {
            $canStart = true;
        } else {
            $hasActiveBreak = DB::table('breaks')
                ->where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->exists();

            if (! is_null($attendance->end_time)) {
                $status   = '退勤済';
                $finished = true;
            } elseif ($hasActiveBreak) {
                $status      = '休憩中';
                $canBreakOut = true;
            } elseif (! is_null($attendance->start_time)) {
                $status     = '出勤中';
                $canEnd     = true;
                $canBreakIn = true;
            } else {
                $status   = '勤務外';
                $canStart = true;
            }
        }

        return view('attendance.index', compact(
            'status',
            'canStart',
            'canEnd',
            'canBreakIn',
            'canBreakOut',
            'finished'
        ));
    }

    public function start(Request $request)
    {
        $user  = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today]
        );

        if (! is_null($attendance->start_time)) {
            return redirect()->route('attendance.index');
        }

        $attendance->start_time = Carbon::now()->format('H:i:s');
        $attendance->status     = '出勤中';
        $attendance->save();

        return redirect()->route('attendance.index');
    }

    public function end(Request $request)
    {
        $user  = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if (! $attendance) {
            return redirect()->route('attendance.index');
        }

        $hasActiveBreak = DB::table('breaks')
            ->where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->exists();

        if (is_null($attendance->end_time)
            && ! is_null($attendance->start_time)
            && ! $hasActiveBreak
        ) {
            $attendance->end_time = Carbon::now()->format('H:i:s');
            $attendance->status   = '退勤済';
            $attendance->save();
        }

        return redirect()->route('attendance.index');
    }

    public function breakStart(Request $request)
    {
        $user  = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if (! $attendance
            || ! is_null($attendance->end_time)
            || is_null($attendance->start_time)
        ) {
            return redirect()->route('attendance.index');
        }

        $hasActiveBreak = DB::table('breaks')
            ->where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->exists();

        if (! $hasActiveBreak) {
            DB::table('breaks')->insert([
                'attendance_id' => $attendance->id,
                'break_start'   => Carbon::now(),
                'break_end'     => null,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ]);

            $attendance->status = '休憩中';
            $attendance->save();
        }

        return redirect()->route('attendance.index');
    }

    public function breakEnd(Request $request)
    {
        $user  = Auth::user();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        if (! $attendance || ! is_null($attendance->end_time)) {
            return redirect()->route('attendance.index');
        }

        $activeBreak = DB::table('breaks')
            ->where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->orderBy('break_start', 'desc')
            ->first();

        if ($activeBreak) {
            DB::table('breaks')
                ->where('id', $activeBreak->id)
                ->update([
                    'break_end'  => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

            $attendance->status = '出勤中';
            $attendance->save();
        }

        return redirect()->route('attendance.index');
    }

    public function list(Request $request)
    {
        $user = Auth::user();

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

        $attendances = Attendance::where('user_id', $user->id)
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

        return view('attendance.list', [
            'attendances'  => $attendances,
            'currentMonth' => $currentMonth,
            'prevMonth'    => $prevMonth,
            'nextMonth'    => $nextMonth,
        ]);
    }

    public function detail($id)
    {
        $user = Auth::user();

        $attendance = Attendance::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $weeks = ['日', '月', '火', '水', '木', '金', '土'];
        $date  = Carbon::parse($attendance->work_date);
        $week  = $weeks[$date->dayOfWeek];

        $breaks = DB::table('breaks')
            ->where('attendance_id', $attendance->id)
            ->orderBy('break_start')
            ->get();

        $break1 = $breaks->get(0);
        $break2 = $breaks->get(1);

        // 承認待ちの修正申請があるか（＝Figmaの「承認待ち勤怠のケース」に切り替える判定）
        $pendingRequest = StampCorrectionRequest::where('user_id', $user->id)
            ->where('attendance_id', $attendance->id)
            ->where('status', '承認待ち')
            ->latest('created_at')
            ->first();

        return view('attendance.detail', [
            'attendance'     => $attendance,
            'user'           => $user,
            'date'           => $date,
            'week'           => $week,
            'break1'         => $break1,
            'break2'         => $break2,
            'pendingRequest' => $pendingRequest,
        ]);
    }
}

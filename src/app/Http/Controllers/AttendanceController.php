<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
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

        $status       = '勤務外';
        $canStart     = false;
        $canEnd       = false;
        $canBreakIn   = false;
        $canBreakOut  = false;
        $finished     = false;

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

    public function list()
    {
        return view('attendance.list');
    }
}

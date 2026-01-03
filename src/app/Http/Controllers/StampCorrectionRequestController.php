<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StampCorrectionRequestController extends Controller
{
    public function list()
    {
        $user = Auth::user();

        $requests = StampCorrectionRequest::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        return view('stamp_correction_request.list', compact('requests'));
    }

    public function store(Request $request)
    {

        $user = Auth::user();

        $validated = $request->validate([
            'attendance_id' => ['required', 'integer'],
            'start_time'    => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'end_time'      => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'break1_start'  => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'break1_end'    => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'break2_start'  => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'break2_end'    => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'remark'        => ['nullable', 'string', 'max:255'],
        ]);

        $attendance = Attendance::where('id', $validated['attendance_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        $alreadyPending = StampCorrectionRequest::where('user_id', $user->id)
            ->where('attendance_id', $attendance->id)
            ->where('status', '承認待ち')
            ->exists();

        if ($alreadyPending) {
            return redirect()->route('attendance.detail', ['id' => $attendance->id]);
        }

        StampCorrectionRequest::create([
    'user_id'       => $user->id,
    'attendance_id' => $attendance->id,

    'corrected_start' => $this->toTimeOrNull($validated['start_time'] ?? null),
    'corrected_end'   => $this->toTimeOrNull($validated['end_time'] ?? null),

    'break1_start' => $this->toTimeOrNull($validated['break1_start'] ?? null),
    'break1_end'   => $this->toTimeOrNull($validated['break1_end'] ?? null),
    'break2_start' => $this->toTimeOrNull($validated['break2_start'] ?? null),
    'break2_end'   => $this->toTimeOrNull($validated['break2_end'] ?? null),

    'remark' => $validated['remark'] ?? null,
    'status' => '承認待ち',
]);


        return redirect()->route('attendance.detail', ['id' => $attendance->id]);
    }

    private function toTimeOrNull(?string $hhmm): ?string
    {
        if (! $hhmm) {
            return null;
        }

        return Carbon::createFromFormat('H:i', $hhmm)->format('H:i:s');
    }
}

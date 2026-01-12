<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StampCorrectionRequestController extends Controller
{
    public function list(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $base = StampCorrectionRequest::query()
            ->where('user_id', Auth::id())
            ->with(['user', 'attendance'])
            ->orderByDesc('created_at');

        $pendingRequests = (clone $base)->where('status', '承認待ち')->get();
        $approvedRequests = (clone $base)->where('status', '承認済み')->get();

        return view('stamp_correction_request.list', compact('tab', 'pendingRequests', 'approvedRequests'));
    }

    public function show(int $id)
    {
        $correctionRequest = StampCorrectionRequest::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['attendance'])
            ->firstOrFail();

        return redirect()->route('attendance.detail', ['id' => $correctionRequest->attendance_id]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'attendance_id' => [
                'required',
                'integer',
                Rule::exists('attendances', 'id')->where(fn ($q) => $q->where('user_id', $user->id)),
            ],
            'start_time'    => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'end_time'      => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'break1_start'  => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'break1_end'    => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'break2_start'  => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'break2_end'    => ['nullable', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'remark'        => ['nullable', 'string', 'max:255'],
        ]);

        $attendanceId = (int) $validated['attendance_id'];

        $alreadyPending = StampCorrectionRequest::query()
            ->where('user_id', $user->id)
            ->where('attendance_id', $attendanceId)
            ->where('status', '承認待ち')
            ->exists();

        if ($alreadyPending) {
            return redirect()
                ->route('attendance.detail', ['id' => $attendanceId])
                ->with('message', 'すでに承認待ちの申請があります');
        }

        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendanceId,
            'corrected_start' => $this->toTimeOrNull($validated['start_time'] ?? null),
            'corrected_end'   => $this->toTimeOrNull($validated['end_time'] ?? null),
            'break1_start' => $this->toTimeOrNull($validated['break1_start'] ?? null),
            'break1_end'   => $this->toTimeOrNull($validated['break1_end'] ?? null),
            'break2_start' => $this->toTimeOrNull($validated['break2_start'] ?? null),
            'break2_end'   => $this->toTimeOrNull($validated['break2_end'] ?? null),
            'remark' => $validated['remark'] ?? null,
            'status' => '承認待ち',
        ]);

        return redirect()
            ->route('attendance.detail', ['id' => $attendanceId])
            ->with('message', '修正申請を送信しました');
    }

    private function toTimeOrNull(?string $hhmm): ?string
    {
        if (! $hhmm) {
            return null;
        }

        return Carbon::createFromFormat('H:i', $hhmm)->format('H:i:s');
    }
}
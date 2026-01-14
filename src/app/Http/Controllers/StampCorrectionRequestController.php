<?php

namespace App\Http\Controllers;

use App\Http\Requests\StampCorrectionRequest as StampCorrectionRequestRequest;
use App\Models\StampCorrectionRequest as StampCorrectionRequestModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    public function list(\Illuminate\Http\Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $base = StampCorrectionRequestModel::query()
            ->where('user_id', Auth::id())
            ->with(['user', 'attendance'])
            ->orderByDesc('created_at');

        $pendingRequests = (clone $base)->where('status', '承認待ち')->get();
        $approvedRequests = (clone $base)->where('status', '承認済み')->get();

        return view('stamp_correction_request.list', compact('tab', 'pendingRequests', 'approvedRequests'));
    }

    public function show(int $id)
    {
        $correctionRequest = StampCorrectionRequestModel::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['attendance'])
            ->firstOrFail();

        return redirect()->route('attendance.detail', ['id' => $correctionRequest->attendance_id]);
    }

    public function store(StampCorrectionRequestRequest $request)
    {
        $user = Auth::user();

        $validated = $request->validated();
        $attendanceId = (int) $validated['attendance_id'];

        $alreadyPending = StampCorrectionRequestModel::query()
            ->where('user_id', $user->id)
            ->where('attendance_id', $attendanceId)
            ->where('status', '承認待ち')
            ->exists();

        if ($alreadyPending) {
            return redirect()
                ->route('attendance.detail', ['id' => $attendanceId])
                ->with('message', 'すでに承認待ちの申請があります');
        }

        StampCorrectionRequestModel::create([
            'user_id' => $user->id,
            'attendance_id' => $attendanceId,
            'corrected_start' => $this->toTimeOrNull($validated['start_time'] ?? null),
            'corrected_end' => $this->toTimeOrNull($validated['end_time'] ?? null),
            'break1_start' => $this->toTimeOrNull($validated['break1_start'] ?? null),
            'break1_end' => $this->toTimeOrNull($validated['break1_end'] ?? null),
            'break2_start' => $this->toTimeOrNull($validated['break2_start'] ?? null),
            'break2_end' => $this->toTimeOrNull($validated['break2_end'] ?? null),
            'remark' => $validated['remark'],
            'status' => '承認待ち',
        ]);

        return redirect()
            ->route('attendance.detail', ['id' => $attendanceId])
            ->with('message', '修正申請を送信しました');
    }

    private function toTimeOrNull(?string $hhmm): ?string
    {
        if (!$hhmm) {
            return null;
        }

        return Carbon::createFromFormat('H:i', $hhmm)->format('H:i:s');
    }
}
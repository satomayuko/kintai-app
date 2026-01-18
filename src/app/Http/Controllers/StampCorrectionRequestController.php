<?php

namespace App\Http\Controllers;

use App\Http\Requests\StampCorrectionRequest as StampCorrectionRequestRequest;
use App\Models\StampCorrectionRequest as StampCorrectionRequestModel;
use App\Models\StampCorrectionRequestBreak;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StampCorrectionRequestController extends Controller
{
    public function list(Request $request)
    {
        $tab = $request->query('tab', 'pending');

        $base = StampCorrectionRequestModel::query()
            ->where('user_id', Auth::id())
            ->with(['user', 'attendance', 'breaks'])
            ->orderByDesc('created_at');

        $pendingRequests = (clone $base)->where('status', 0)->get();
        $approvedRequests = (clone $base)->where('status', 1)->get();

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
            ->where('status', 0)
            ->exists();

        if ($alreadyPending) {
            return redirect()->route('attendance.detail', ['id' => $attendanceId]);
        }

        DB::transaction(function () use ($user, $attendanceId, $validated) {
            $correctionRequest = StampCorrectionRequestModel::create([
                'user_id' => $user->id,
                'attendance_id' => $attendanceId,
                'corrected_start' => $this->toTimeOrNull($validated['start_time'] ?? null),
                'corrected_end' => $this->toTimeOrNull($validated['end_time'] ?? null),
                'remark' => $validated['remark'] ?? '',
                'status' => 0,
            ]);

            $breakRows = $validated['breaks'] ?? [];
            if (!is_array($breakRows)) {
                $breakRows = [];
            }

            $sortOrder = 1;

            foreach ($breakRows as $row) {
                $startStr = $row['break_start'] ?? null;
                $endStr = $row['break_end'] ?? null;

                $startStr = ($startStr === '') ? null : $startStr;
                $endStr = ($endStr === '') ? null : $endStr;

                $start = $this->toTimeOrNull($startStr);
                $end = $this->toTimeOrNull($endStr);

                if ($start === null && $end === null) {
                    continue;
                }

                StampCorrectionRequestBreak::create([
                    'stamp_correction_request_id' => $correctionRequest->id,
                    'break_start' => $start,
                    'break_end' => $end,
                    'sort_order' => $sortOrder,
                ]);

                $sortOrder++;
            }
        });

        return redirect()->route('attendance.detail', ['id' => $attendanceId]);
    }

    private function toTimeOrNull(?string $hhmm): ?string
    {
        if ($hhmm === null || $hhmm === '') {
            return null;
        }

        return Carbon::createFromFormat('H:i', $hhmm)->format('H:i:s');
    }
}
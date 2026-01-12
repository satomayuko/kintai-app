<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StampCorrectionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminCorrectionController extends Controller
{
    public function index(Request $httpRequest): View
    {
        $tab = $httpRequest->query('tab', 'pending');

        $base = StampCorrectionRequest::query()
            ->with(['user', 'attendance'])
            ->orderByDesc('created_at');

        $pendingRequests = (clone $base)->where('status', '承認待ち')->get();
        $approvedRequests = (clone $base)->where('status', '承認済み')->get();

        return view('admin.request.list', compact('tab', 'pendingRequests', 'approvedRequests'));
    }

    public function show(int $id): View
    {
        $correctionRequest = StampCorrectionRequest::query()
            ->with(['user', 'attendance'])
            ->findOrFail($id);

        return view('admin.request.approve', compact('correctionRequest'));
    }

    public function approveForm(int $attendance_correct_request_id): View
    {
        $correctionRequest = StampCorrectionRequest::query()
            ->with(['user', 'attendance.breaks'])
            ->findOrFail($attendance_correct_request_id);

        return view('admin.request.approve', compact('correctionRequest'));
    }

    public function approve(Request $request, int $attendance_correct_request_id): RedirectResponse|JsonResponse
    {
        $correctionRequest = StampCorrectionRequest::query()
            ->with(['attendance.breaks'])
            ->findOrFail($attendance_correct_request_id);

        if ($correctionRequest->status !== '承認待ち') {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false], 422);
            }

            return redirect()->route('admin.stamp_correction_request.list');
        }

        DB::transaction(function () use ($correctionRequest) {
            $attendance = $correctionRequest->attendance;
            $workDate = Carbon::parse($attendance->work_date ?? now())->toDateString();

            $toDateTimeString = function ($value) use ($workDate): ?string {
                if ($value === null || $value === '') {
                    return null;
                }
                $v = trim((string) $value);
                if (str_contains($v, '-')) {
                    return Carbon::parse($v)->format('Y-m-d H:i:s');
                }
                return Carbon::parse($workDate . ' ' . $v)->format('Y-m-d H:i:s');
            };

            $attendance->fill([
                'start_time' => $correctionRequest->corrected_start ?? $attendance->start_time,
                'end_time'   => $correctionRequest->corrected_end ?? $attendance->end_time,
                'remark'     => $correctionRequest->remark ?? $attendance->remark,
            ])->save();

            $breaks = $attendance->breaks->values();
            $break1 = $breaks->get(0);
            $break2 = $breaks->get(1);

            if (!is_null($correctionRequest->break1_start) || !is_null($correctionRequest->break1_end)) {
                $payload = [
                    'break_start' => $toDateTimeString($correctionRequest->break1_start),
                    'break_end'   => $toDateTimeString($correctionRequest->break1_end),
                ];

                if ($break1) {
                    $break1->update($payload);
                } else {
                    $attendance->breaks()->create($payload);
                }
            }

            if (!is_null($correctionRequest->break2_start) || !is_null($correctionRequest->break2_end)) {
                $payload = [
                    'break_start' => $toDateTimeString($correctionRequest->break2_start),
                    'break_end'   => $toDateTimeString($correctionRequest->break2_end),
                ];

                if ($break2) {
                    $break2->update($payload);
                } else {
                    $attendance->breaks()->create($payload);
                }
            }

            $correctionRequest->update(['status' => '承認済み']);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'status' => '承認済み',
            ]);
        }

        return redirect()->route('admin.stamp_correction_request.list', ['tab' => 'approved']);
    }
}
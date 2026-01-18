<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StampCorrectionRequest;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $pendingRequests  = (clone $base)->where('status', 0)->get();
        $approvedRequests = (clone $base)->where('status', 1)->get();

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
            ->with(['user', 'attendance.breaks', 'breaks'])
            ->findOrFail($attendance_correct_request_id);

        return view('admin.request.approve', compact('correctionRequest'));
    }

    public function approve(Request $request, int $attendance_correct_request_id): RedirectResponse|JsonResponse
    {
        $correctionRequest = StampCorrectionRequest::query()
            ->with(['attendance.breaks', 'breaks'])
            ->findOrFail($attendance_correct_request_id);

        if ((int) $correctionRequest->status !== 0) {
            if ($request->expectsJson()) {
                return response()->json(['ok' => false], 422);
            }

            return redirect()->route('admin.stamp_correction_request.list');
        }

        DB::transaction(function () use ($correctionRequest) {
            $attendance = $correctionRequest->attendance;

            $attendance->fill([
                'start_time' => $correctionRequest->corrected_start ?? $attendance->start_time,
                'end_time'   => $correctionRequest->corrected_end ?? $attendance->end_time,
                'remark'     => $correctionRequest->remark ?? $attendance->remark,
            ])->save();

            DB::table('breaks')->where('attendance_id', $attendance->id)->delete();

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

            $requestBreaks = $correctionRequest->breaks->values();

            foreach ($requestBreaks as $b) {
                DB::table('breaks')->insert([
                    'attendance_id' => $attendance->id,
                    'break_start'   => $toDateTimeString($b->break_start),
                    'break_end'     => $toDateTimeString($b->break_end),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ]);
            }

            $correctionRequest->update(['status' => 1]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'status' => 1,
            ]);
        }

        return redirect()->route('admin.stamp_correction_request.list', ['tab' => 'approved']);
    }
}
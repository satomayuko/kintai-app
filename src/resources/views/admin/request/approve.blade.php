@extends('layouts.default')

@section('title', '修正申請承認（管理者）')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/detail.css') }}">
<link rel="stylesheet" href="{{ asset('css/admin/request/approve.css') }}">
@endsection

@section('content')
@include('components.header')

@php
    use Illuminate\Support\Carbon;

    $attendance = $correctionRequest->attendance;
    $user = $correctionRequest->user;

    $workDate = Carbon::parse($attendance->work_date ?? now());

    $formatDash = fn ($t) => $t ? Carbon::parse($t)->format('H:i') : '-';

    $start = $formatDash($correctionRequest->corrected_start ?? $attendance->start_time);
    $end   = $formatDash($correctionRequest->corrected_end ?? $attendance->end_time);

    $reqBreaks = $correctionRequest->relationLoaded('breaks')
        ? $correctionRequest->breaks->sortBy('sort_order')->values()
        : $correctionRequest->breaks()->orderBy('sort_order')->get()->values();

    $filledBreaks = $reqBreaks
        ->filter(fn ($b) => !is_null($b->break_start) || !is_null($b->break_end))
        ->values();

    if ($filledBreaks->isEmpty()) {
        $breakSlots = collect([
            ['label' => '休憩',  'start' => '00:00', 'end' => '00:00', 'has' => false],
            ['label' => '休憩2', 'start' => '00:00', 'end' => '00:00', 'has' => false],
        ]);
    } else {
        $breakSlots = $filledBreaks->map(function ($b, $idx) use ($formatDash) {
            $i = $idx + 1;
            $bs = $b->break_start ?? null;
            $be = $b->break_end ?? null;

            return [
                'label' => $i === 1 ? '休憩' : "休憩{$i}",
                'start' => $formatDash($bs),
                'end'   => $formatDash($be),
                'has'   => true,
            ];
        });
    }

    $remark = $correctionRequest->remark ?? $attendance->remark ?? '';

    $approveParam = ['attendance_correct_request_id' => $correctionRequest->id];

    $statusRaw = $correctionRequest->status;
    $isApproved = ((string) $statusRaw === '承認済み') || ((int) $statusRaw === 1);
@endphp

<div class="attendance-detail-page admin-approve">
    <div class="attendance-detail-inner">

        <div class="attendance-detail-title">
            <span class="attendance-detail-title-line"></span>
            <h1 class="attendance-detail-title-text">勤怠詳細</h1>
        </div>

        <div class="attendance-detail-card">

            <div class="attendance-detail-row attendance-detail-row--name">
                <div class="attendance-detail-label">名前</div>
                <div class="attendance-detail-value attendance-detail-value--time-range">
                    <span class="time-text">{{ $user->name }}</span>
                    <span class="time-separator attendance-detail-placeholder" aria-hidden="true">〜</span>
                    <span class="time-text attendance-detail-placeholder" aria-hidden="true">00:00</span>
                </div>
                <div></div>
            </div>

            <div class="attendance-detail-row">
                <div class="attendance-detail-label">日付</div>
                <div class="attendance-detail-value attendance-detail-value--time-range">
                    <span class="time-text">{{ $workDate->format('Y年') }}</span>
                    <span class="time-separator attendance-detail-placeholder" aria-hidden="true">〜</span>
                    <span class="time-text">{{ $workDate->format('n月j日') }}</span>
                </div>
                <div></div>
            </div>

            <div class="attendance-detail-row">
                <div class="attendance-detail-label">出勤・退勤</div>
                <div class="attendance-detail-value attendance-detail-value--time-range">
                    <span class="time-text">{{ $start }}</span>
                    <span class="time-separator">〜</span>
                    <span class="time-text">{{ $end }}</span>
                </div>
                <div></div>
            </div>

            @foreach($breakSlots as $slot)
                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">{{ $slot['label'] }}</div>
                    <div class="attendance-detail-value attendance-detail-value--time-range">
                        @if($slot['has'])
                            <span class="time-text">{{ $slot['start'] }}</span>
                            <span class="time-separator">〜</span>
                            <span class="time-text">{{ $slot['end'] }}</span>
                        @else
                            <span class="time-text attendance-detail-placeholder" aria-hidden="true">00:00</span>
                            <span class="time-separator attendance-detail-placeholder" aria-hidden="true">〜</span>
                            <span class="time-text attendance-detail-placeholder" aria-hidden="true">00:00</span>
                        @endif
                    </div>
                    <div></div>
                </div>
            @endforeach

            <div class="attendance-detail-row attendance-detail-row--remark">
                <div class="attendance-detail-label">備考</div>
                <div class="attendance-detail-value attendance-detail-value--time-range">
                    <span class="time-text">{{ $remark }}</span>
                    <span class="time-separator attendance-detail-placeholder" aria-hidden="true">〜</span>
                    <span class="time-text attendance-detail-placeholder" aria-hidden="true">00:00</span>
                </div>
                <div></div>
            </div>

        </div>

        <div class="attendance-detail-footer">
            @if($isApproved)
                <button class="attendance-detail-edit-button is-disabled" disabled>承認済み</button>
            @else
                <form action="{{ route('admin.stamp_correction_request.approve', $approveParam) }}" method="POST">
                    @csrf
                    <button type="submit" class="attendance-detail-edit-button">承認</button>
                </form>
            @endif
        </div>

    </div>
</div>
@endsection
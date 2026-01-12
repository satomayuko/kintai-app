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

    $formatDash  = fn($t) => $t ? Carbon::parse($t)->format('H:i') : '-';
    $formatEmpty = fn($t) => $t ? Carbon::parse($t)->format('H:i') : '';

    $start = $formatDash($correctionRequest->corrected_start ?? $attendance->start_time);
    $end   = $formatDash($correctionRequest->corrected_end ?? $attendance->end_time);

    $break1Start = $formatEmpty($correctionRequest->break1_start);
    $break1End   = $formatEmpty($correctionRequest->break1_end);
    $break2Start = $formatEmpty($correctionRequest->break2_start);
    $break2End   = $formatEmpty($correctionRequest->break2_end);

    $hasBreak1 = ($break1Start !== '') || ($break1End !== '');
    $hasBreak2 = ($break2Start !== '') || ($break2End !== '');

    $remark = $correctionRequest->remark ?? $attendance->remark;

    $approveParam = ['attendance_correct_request_id' => $correctionRequest->id];
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

            <div class="attendance-detail-row">
                <div class="attendance-detail-label">休憩</div>
                <div class="attendance-detail-value attendance-detail-value--time-range">
                    @if($hasBreak1)
                        <span class="time-text">{{ $break1Start !== '' ? $break1Start : '-' }}</span>
                        <span class="time-separator">〜</span>
                        <span class="time-text">{{ $break1End !== '' ? $break1End : '-' }}</span>
                    @else
                        <span class="time-text attendance-detail-placeholder" aria-hidden="true">00:00</span>
                        <span class="time-separator attendance-detail-placeholder" aria-hidden="true">〜</span>
                        <span class="time-text attendance-detail-placeholder" aria-hidden="true">00:00</span>
                    @endif
                </div>
                <div></div>
            </div>

            <div class="attendance-detail-row">
                <div class="attendance-detail-label">休憩2</div>
                <div class="attendance-detail-value attendance-detail-value--time-range">
                    @if($hasBreak2)
                        <span class="time-text">{{ $break2Start !== '' ? $break2Start : '-' }}</span>
                        <span class="time-separator">〜</span>
                        <span class="time-text">{{ $break2End !== '' ? $break2End : '-' }}</span>
                    @else
                        <span class="time-text attendance-detail-placeholder" aria-hidden="true">00:00</span>
                        <span class="time-separator attendance-detail-placeholder" aria-hidden="true">〜</span>
                        <span class="time-text attendance-detail-placeholder" aria-hidden="true">00:00</span>
                    @endif
                </div>
                <div></div>
            </div>

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
            @if($correctionRequest->status === '承認済み')
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
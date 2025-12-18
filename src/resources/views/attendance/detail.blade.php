@extends('layouts.default')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance/detail.css') }}">
@endsection

@section('content')

@include('components.header')

<div class="attendance-detail-page">
    <div class="attendance-detail-inner">

        <div class="attendance-detail-title">
            <span class="attendance-detail-title-line"></span>
            <span class="attendance-detail-title-text">勤怠詳細</span>
        </div>

        <div class="attendance-detail-card">

            <div class="attendance-detail-row">
                <div class="attendance-detail-label">名前</div>
                <div class="attendance-detail-value">
                    {{ $user->name }}
                </div>
            </div>

            <div class="attendance-detail-row">
    <div class="attendance-detail-label">日付</div>
    <div class="attendance-detail-value attendance-detail-value--date">
        <span class="date-year">
            {{ $date->format('Y年') }}
        </span>
        <span class="date-spacer"></span>
        <span class="date-day">
            {{ $date->format('n月j日') }}
        </span>
    </div>
</div>


            <div class="attendance-detail-row">
                <div class="attendance-detail-label">出勤・退勤</div>
                <div class="attendance-detail-value attendance-detail-value--time-range">
                    <span class="time-box">
                        @if ($attendance->start_time)
                            {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}
                        @else
                            -
                        @endif
                    </span>
                    <span class="time-separator">〜</span>
                    <span class="time-box">
                        @if ($attendance->end_time)
                            {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}
                        @else
                            -
                        @endif
                    </span>
                </div>
            </div>

            <div class="attendance-detail-row">
                <div class="attendance-detail-label">休憩</div>
                <div class="attendance-detail-value attendance-detail-value--time-range">
                    <span class="time-box">
                        @if ($break1 && $break1->break_start)
                            {{ \Carbon\Carbon::parse($break1->break_start)->format('H:i') }}
                        @else
                            -
                        @endif
                    </span>
                    <span class="time-separator">〜</span>
                    <span class="time-box">
                        @if ($break1 && $break1->break_end)
                            {{ \Carbon\Carbon::parse($break1->break_end)->format('H:i') }}
                        @else
                            -
                        @endif
                    </span>
                </div>
            </div>

            <div class="attendance-detail-row">
                <div class="attendance-detail-label">休憩2</div>
                <div class="attendance-detail-value attendance-detail-value--time-range">
                    <span class="time-box">
                        @if ($break2 && $break2->break_start)
                            {{ \Carbon\Carbon::parse($break2->break_start)->format('H:i') }}
                        @else
                            -
                        @endif
                    </span>
                    <span class="time-separator">〜</span>
                    <span class="time-box">
                        @if ($break2 && $break2->break_end)
                            {{ \Carbon\Carbon::parse($break2->break_end)->format('H:i') }}
                        @else
                            -
                        @endif
                    </span>
                </div>
            </div>

            <div class="attendance-detail-row attendance-detail-row--remark">
                <div class="attendance-detail-label">備考</div>
                <div class="attendance-detail-value">
                    <div class="attendance-detail-remark-box">
                        <span class="attendance-detail-remark-text">
                            {{ $attendance->remark ?? '' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <div class="attendance-detail-footer">
            <a href="{{ route('stamp_correction_request.list') }}" class="attendance-detail-edit-button">
                修正
            </a>
        </div>

    </div>
</div>

@endsection

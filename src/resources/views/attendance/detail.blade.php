@extends('layouts.default')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance/detail.css') }}">
@endsection

@section('content')

@include('components.header')

@php
    $fmt = function ($time) {
        return $time ? \Carbon\Carbon::parse($time)->format('H:i') : '';
    };

    $isPending = !empty($pendingRequest);

    $displayStart = $isPending ? $fmt($pendingRequest->corrected_start) : $fmt($attendance->start_time);
    $displayEnd   = $isPending ? $fmt($pendingRequest->corrected_end)   : $fmt($attendance->end_time);

    $displayBreak1Start = $isPending ? $fmt($pendingRequest->break1_start ?? null) : $fmt($break1->break_start ?? null);
    $displayBreak1End   = $isPending ? $fmt($pendingRequest->break1_end ?? null)   : $fmt($break1->break_end ?? null);

    $displayBreak2Start = $isPending ? $fmt($pendingRequest->break2_start ?? null) : $fmt($break2->break_start ?? null);
    $displayBreak2End   = $isPending ? $fmt($pendingRequest->break2_end ?? null)   : $fmt($break2->break_end ?? null);
@endphp

<div class="attendance-detail-page">
    <div class="attendance-detail-inner">

        <div class="attendance-detail-title">
            <span class="attendance-detail-title-line"></span>
            <span class="attendance-detail-title-text">勤怠詳細</span>
        </div>

        @if($isPending)

            <div class="attendance-detail-card">

                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">名前</div>
                    <div class="attendance-detail-value">{{ $user->name }}</div>
                </div>

                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">日付</div>
                    <div class="attendance-detail-value attendance-detail-value--date">
                        <span class="date-year">{{ $date->format('Y年') }}</span>
                        <span class="date-spacer"></span>
                        <span class="date-day">{{ $date->format('n月j日') }}</span>
                    </div>
                </div>

                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">出勤・退勤</div>
                    <div class="attendance-detail-value attendance-detail-value--time-range">
                        <span class="time-text">{{ $displayStart }}</span>
                        <span class="time-separator">〜</span>
                        <span class="time-text">{{ $displayEnd }}</span>
                    </div>
                </div>

                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">休憩</div>
                    <div class="attendance-detail-value attendance-detail-value--time-range">
                        <span class="time-text">{{ $displayBreak1Start }}</span>
                        <span class="time-separator">〜</span>
                        <span class="time-text">{{ $displayBreak1End }}</span>
                    </div>
                </div>

                @if($displayBreak2Start && $displayBreak2End)
                <div class="attendance-detail-row">
                    <div class="attendance-detail-label">休憩2</div>
                    <div class="attendance-detail-value attendance-detail-value--time-range">
                        <span class="time-text">{{ $displayBreak2Start }}</span>
                        <span class="time-separator">〜</span>
                        <span class="time-text">{{ $displayBreak2End }}</span>
                    </div>
                </div>
                @endif

                <div class="attendance-detail-row attendance-detail-row--remark">
                    <div class="attendance-detail-label">備考</div>
                    <div class="attendance-detail-value">
                        <div class="attendance-detail-remark-text">
                            {{ $pendingRequest->remark ?? '' }}
                        </div>
                    </div>
                </div>

            </div>

            <p class="attendance-detail-warning">*承認待ちのため修正はできません。</p>

        @else

            <form action="{{ route('stamp_correction_request.store') }}" method="POST">
                @csrf
                <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

                <div class="attendance-detail-card">

                    <div class="attendance-detail-row">
                        <div class="attendance-detail-label">名前</div>
                        <div class="attendance-detail-value">{{ $user->name }}</div>
                    </div>

                    <div class="attendance-detail-row">
                        <div class="attendance-detail-label">日付</div>
                        <div class="attendance-detail-value attendance-detail-value--date">
                            <span class="date-year">{{ $date->format('Y年') }}</span>
                            <span class="date-spacer"></span>
                            <span class="date-day">{{ $date->format('n月j日') }}</span>
                        </div>
                    </div>

                    <div class="attendance-detail-row">
                        <div class="attendance-detail-label">出勤・退勤</div>
                        <div class="attendance-detail-value attendance-detail-value--time-range">
                            <span class="time-box">
                                <input type="time"
                                    name="start_time"
                                    value="{{ old('start_time', $fmt($attendance->start_time)) }}"
                                    class="attendance-detail-input attendance-detail-input--time"
                                    step="60">
                            </span>
                            <span class="time-separator">〜</span>
                            <span class="time-box">
                                <input type="time"
                                    name="end_time"
                                    value="{{ old('end_time', $fmt($attendance->end_time)) }}"
                                    class="attendance-detail-input attendance-detail-input--time"
                                    step="60">
                            </span>
                        </div>
                    </div>

                    <div class="attendance-detail-row">
                        <div class="attendance-detail-label">休憩</div>
                        <div class="attendance-detail-value attendance-detail-value--time-range">
                            <span class="time-box">
                                <input type="time"
                                    name="break1_start"
                                    value="{{ old('break1_start', $fmt($break1->break_start ?? null)) }}"
                                    class="attendance-detail-input attendance-detail-input--time"
                                    step="60">
                            </span>
                            <span class="time-separator">〜</span>
                            <span class="time-box">
                                <input type="time"
                                    name="break1_end"
                                    value="{{ old('break1_end', $fmt($break1->break_end ?? null)) }}"
                                    class="attendance-detail-input attendance-detail-input--time"
                                    step="60">
                            </span>
                        </div>
                    </div>

                    <div class="attendance-detail-row">
                        <div class="attendance-detail-label">休憩2</div>
                        <div class="attendance-detail-value attendance-detail-value--time-range">
                            <span class="time-box">
                                <input type="time"
                                    name="break2_start"
                                    value="{{ old('break2_start', $fmt($break2->break_start ?? null)) }}"
                                    class="attendance-detail-input attendance-detail-input--time"
                                    step="60">
                            </span>
                            <span class="time-separator">〜</span>
                            <span class="time-box">
                                <input type="time"
                                    name="break2_end"
                                    value="{{ old('break2_end', $fmt($break2->break_end ?? null)) }}"
                                    class="attendance-detail-input attendance-detail-input--time"
                                    step="60">
                            </span>
                        </div>
                    </div>

                    <div class="attendance-detail-row attendance-detail-row--remark">
                        <div class="attendance-detail-label">備考</div>
                        <div class="attendance-detail-value">
                            <div class="attendance-detail-remark-box">
                                <textarea name="remark" class="attendance-detail-textarea">{{ old('remark', $attendance->remark ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="attendance-detail-footer">
                    <button type="submit" class="attendance-detail-edit-button">修正</button>
                </div>
            </form>

        @endif

    </div>
</div>

@endsection

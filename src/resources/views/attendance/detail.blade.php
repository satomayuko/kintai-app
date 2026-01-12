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

        $hasRequest = !empty($latestRequest);
        $requestStatus = $hasRequest ? ($latestRequest->status ?? null) : null;

        $isPending = $requestStatus === '承認待ち';
        $isApproved = $requestStatus === '承認済み';
        $canEdit = !$hasRequest || $requestStatus === '却下';

        $displayStart = $hasRequest ? $fmt($latestRequest->corrected_start) : $fmt($attendance->start_time);
        $displayEnd = $hasRequest ? $fmt($latestRequest->corrected_end) : $fmt($attendance->end_time);

        $displayBreak1Start = $hasRequest ? $fmt($latestRequest->break1_start ?? null) : $fmt($break1->break_start ?? null);
        $displayBreak1End = $hasRequest ? $fmt($latestRequest->break1_end ?? null) : $fmt($break1->break_end ?? null);

        $displayBreak2Start = $hasRequest ? $fmt($latestRequest->break2_start ?? null) : $fmt($break2->break_start ?? null);
        $displayBreak2End = $hasRequest ? $fmt($latestRequest->break2_end ?? null) : $fmt($break2->break_end ?? null);

        $displayRemark = $hasRequest ? ($latestRequest->remark ?? '') : ($attendance->remark ?? '');

        $vStart = old('start_time', $displayStart);
        $vEnd = old('end_time', $displayEnd);

        $vB1S = old('break1_start', $displayBreak1Start);
        $vB1E = old('break1_end', $displayBreak1End);

        $vB2S = old('break2_start', $displayBreak2Start);
        $vB2E = old('break2_end', $displayBreak2End);

        $vRemark = old('remark', $displayRemark);
    @endphp

    <div class="attendance-detail-page">
        <div class="attendance-detail-inner">

            <div class="attendance-detail-title">
                <span class="attendance-detail-title-line"></span>
                <span class="attendance-detail-title-text">勤怠詳細</span>
            </div>

            @if(!$canEdit)

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
                                {{ $displayRemark }}
                            </div>
                        </div>
                    </div>

                </div>

                @if($isPending)
                    <p class="attendance-detail-warning">*承認待ちのため修正はできません。</p>
                @elseif($isApproved)
                    <p class="attendance-detail-warning">*承認済みのため修正はできません。</p>
                @endif

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
                                           value="{{ $vStart }}"
                                           class="attendance-detail-input attendance-detail-input--time {{ $vStart === '' ? 'is-empty' : '' }}"
                                           step="60">
                                </span>
                                <span class="time-separator">〜</span>
                                <span class="time-box">
                                    <input type="time"
                                           name="end_time"
                                           value="{{ $vEnd }}"
                                           class="attendance-detail-input attendance-detail-input--time {{ $vEnd === '' ? 'is-empty' : '' }}"
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
                                           value="{{ $vB1S }}"
                                           class="attendance-detail-input attendance-detail-input--time {{ $vB1S === '' ? 'is-empty' : '' }}"
                                           step="60">
                                </span>
                                <span class="time-separator">〜</span>
                                <span class="time-box">
                                    <input type="time"
                                           name="break1_end"
                                           value="{{ $vB1E }}"
                                           class="attendance-detail-input attendance-detail-input--time {{ $vB1E === '' ? 'is-empty' : '' }}"
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
                                           value="{{ $vB2S }}"
                                           class="attendance-detail-input attendance-detail-input--time {{ $vB2S === '' ? 'is-empty' : '' }}"
                                           step="60">
                                </span>
                                <span class="time-separator">〜</span>
                                <span class="time-box">
                                    <input type="time"
                                           name="break2_end"
                                           value="{{ $vB2E }}"
                                           class="attendance-detail-input attendance-detail-input--time {{ $vB2E === '' ? 'is-empty' : '' }}"
                                           step="60">
                                </span>
                            </div>
                        </div>

                        <div class="attendance-detail-row attendance-detail-row--remark">
                            <div class="attendance-detail-label">備考</div>
                            <div class="attendance-detail-value">
                                <div class="attendance-detail-remark-box">
                                    <textarea name="remark" class="attendance-detail-textarea">{{ $vRemark }}</textarea>
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
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const timeInputs = document.querySelectorAll('.attendance-detail-input--time');

        const sync = (el) => {
            if (el.value && el.value.trim() !== '') {
                el.classList.remove('is-empty');
            } else {
                el.classList.add('is-empty');
            }
        };

        timeInputs.forEach((el) => {
            sync(el);

            el.addEventListener('input', () => sync(el));
            el.addEventListener('change', () => sync(el));
            el.addEventListener('blur', () => sync(el));
        });
    });
    </script>
@endsection
@extends('layouts.default')

@section('title', '勤怠詳細(管理者)')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin/attendance/detail.css') }}">
@endsection

@section('content')
    @include('components.header')

    @php
        $fmt = fn ($t) => $t ? \Carbon\Carbon::parse($t)->format('H:i') : '';

        $user = $attendance->user;
        $date = \Carbon\Carbon::parse($attendance->work_date);

        $breaks = $attendance->breaks->values();
        $break1 = $breaks->get(0);
        $break2 = $breaks->get(1);

        $vStart = old('start_time', $fmt($attendance->start_time));
        $vEnd   = old('end_time',   $fmt($attendance->end_time));

        $vB1S = old('break1_start', $fmt(optional($break1)->break_start));
        $vB1E = old('break1_end',   $fmt(optional($break1)->break_end));

        $vB2S = old('break2_start', $fmt(optional($break2)->break_start));
        $vB2E = old('break2_end',   $fmt(optional($break2)->break_end));

        $vRemark = old('remark', $attendance->remark ?? '');
    @endphp

    <div class="attendance-detail-page">
        <div class="attendance-detail-inner">

            <div class="attendance-detail-title">
                <span class="attendance-detail-title-line"></span>
                <span class="attendance-detail-title-text">勤怠詳細</span>
            </div>

            @if ($errors->any())
                <ul class="attendance-detail-errors">
                    @foreach ($errors->all() as $error)
                        <li class="attendance-detail-error">{{ $error }}</li>
                    @endforeach
                </ul>
            @endif

            <form action="{{ route('admin.attendance.update', ['id' => $attendance->id]) }}" method="POST" novalidate>
                @csrf
                @method('PATCH')

                <div class="attendance-detail-card">

                    <div class="attendance-detail-row">
                        <div class="attendance-detail-label">名前</div>
                        <div class="attendance-detail-value attendance-detail-value--name">{{ $user->name }}</div>
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
                                <input type="time" name="start_time" value="{{ $vStart }}"
                                    class="attendance-detail-input attendance-detail-input--time {{ $vStart === '' ? 'is-empty' : '' }}"
                                    step="60" required>
                            </span>
                            <span class="time-separator">〜</span>
                            <span class="time-box">
                                <input type="time" name="end_time" value="{{ $vEnd }}"
                                    class="attendance-detail-input attendance-detail-input--time {{ $vEnd === '' ? 'is-empty' : '' }}"
                                    step="60" required>
                            </span>
                        </div>
                    </div>

                    <div class="attendance-detail-row">
                        <div class="attendance-detail-label">休憩</div>
                        <div class="attendance-detail-value attendance-detail-value--time-range">
                            <span class="time-box">
                                <input type="time" name="break1_start" value="{{ $vB1S }}"
                                    class="attendance-detail-input attendance-detail-input--time {{ $vB1S === '' ? 'is-empty' : '' }}"
                                    step="60">
                            </span>
                            <span class="time-separator">〜</span>
                            <span class="time-box">
                                <input type="time" name="break1_end" value="{{ $vB1E }}"
                                    class="attendance-detail-input attendance-detail-input--time {{ $vB1E === '' ? 'is-empty' : '' }}"
                                    step="60">
                            </span>
                        </div>
                    </div>

                    <div class="attendance-detail-row">
                        <div class="attendance-detail-label">休憩2</div>
                        <div class="attendance-detail-value attendance-detail-value--time-range">
                            <span class="time-box">
                                <input type="time" name="break2_start" value="{{ $vB2S }}"
                                    class="attendance-detail-input attendance-detail-input--time {{ $vB2S === '' ? 'is-empty' : '' }}"
                                    step="60">
                            </span>
                            <span class="time-separator">〜</span>
                            <span class="time-box">
                                <input type="time" name="break2_end" value="{{ $vB2E }}"
                                    class="attendance-detail-input attendance-detail-input--time {{ $vB2E === '' ? 'is-empty' : '' }}"
                                    step="60">
                            </span>
                        </div>
                    </div>

                    <div class="attendance-detail-row attendance-detail-row--remark">
                        <div class="attendance-detail-label">備考</div>
                        <div class="attendance-detail-value">
                            <div class="attendance-detail-remark-box">
                                <textarea name="remark" class="attendance-detail-textarea" required maxlength="255">{{ $vRemark }}</textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="attendance-detail-footer">
                    <button type="submit" class="attendance-detail-edit-button">修正</button>
                </div>
            </form>

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
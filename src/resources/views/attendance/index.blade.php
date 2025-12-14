@extends('layouts.default')

@section('title', '勤怠登録')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance/index.css') }}">
@endsection

@section('content')

@include('components.header', ['finished' => $finished])

@php
    $week = ['日', '月', '火', '水', '木', '金', '土'];
    $today   = \Carbon\Carbon::today();
    $w       = $week[$today->dayOfWeek];
    $dateText = $today->format('Y年n月j日') . '(' . $w . ')';
@endphp

<div class="center attendance-page">

    <p class="attendance-status">
        {{ $status }}
    </p>

    <p class="attendance-date">
        {{ $dateText }}
    </p>

    <p class="attendance-time" id="clock">
        {{ \Carbon\Carbon::now()->format('H:i') }}
    </p>

    <div class="attendance-buttons">
        @if ($finished)
            <p class="attendance-finished-message">お疲れ様でした。</p>
        @else
            @if ($canStart)
                <form action="{{ route('attendance.start') }}" method="POST">
                    @csrf
                    <button class="attendance-start-button">出勤</button>
                </form>
            @endif

            @if ($canEnd)
                <form action="{{ route('attendance.end') }}" method="POST">
                    @csrf
                    <button class="attendance-end-button">退勤</button>
                </form>
            @endif

            @if ($canBreakIn)
                <form action="{{ route('attendance.break_start') }}" method="POST">
                    @csrf
                    <button class="attendance-break-in-button">休憩入</button>
                </form>
            @endif

            @if ($canBreakOut)
                <form action="{{ route('attendance.break_end') }}" method="POST">
                    @csrf
                    <button class="attendance-break-out-button">休憩戻</button>
                </form>
            @endif
        @endif
    </div>

</div>

<script>
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('clock').textContent = `${h}:${m}`;
    }
    updateClock();
    setInterval(updateClock, 60 * 1000);
</script>

@endsection

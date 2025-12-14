@extends('layouts.default')

@section('title', '勤怠登録')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/attendance/index.css') }}">
@endsection

@section('content')

@include('components.header')

<div class="container center">

    {{-- 日付 --}}
    <p class="attendance__date">
        {{ \Carbon\Carbon::today()->format('Y年n月j日(D)') }}
    </p>

    {{-- 現在時刻 --}}
    <p class="attendance__time" id="clock">
        {{ \Carbon\Carbon::now()->format('H:i') }}
    </p>

    {{-- 「出勤」ボタン --}}
    <form action="/attendance/start" method="post">
        @csrf
        <button class="btn btn--big">出勤</button>
    </form>

    {{-- 「勤務外」ボタン --}}
    <form action="/attendance/out" method="post">
        @csrf
        <button class="btn2 btn2--big">勤務外</button>
    </form>

</div>

<script>
    // 時刻を1秒ごとに更新（Figma の仕様に合わせてリアル時計にする場合）
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('clock').textContent = `${h}:${m}`;
    }
    setInterval(updateClock, 1000);
</script>

@endsection

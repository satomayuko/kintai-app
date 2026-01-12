@extends('layouts.default')

@section('title', '勤怠一覧（管理者）')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance/list.css') }}">
@endsection

@section('content')

@include('components.header')

<div class="attendance-list-page">
    <div class="attendance-list-inner">

        <div class="attendance-list-title">
            <span class="attendance-list-title-line"></span>
            <span class="attendance-list-title-text">{{ $currentDate->format('Y年n月j日') }}の勤怠</span>
        </div>

        <div class="attendance-list-month-bar">
            <a href="{{ route('admin.attendance.list', ['date' => $prevDate->format('Y-m-d')]) }}" class="month-nav month-nav--prev">
                <img src="{{ asset('img/icon-arrow-left.png') }}" alt="前日" class="month-nav__icon">
                <span class="month-nav__label">前日</span>
            </a>

            <div class="month-center">
                <img src="{{ asset('img/icon-calendar.png') }}" alt="" class="month-center__icon">
                <span class="month-center__text">{{ $currentDate->format('Y/m/d') }}</span>
            </div>

            <a href="{{ route('admin.attendance.list', ['date' => $nextDate->format('Y-m-d')]) }}" class="month-nav month-nav--next">
                <span class="month-nav__label">翌日</span>
                <img src="{{ asset('img/icon-arrow-right.png') }}" alt="翌日" class="month-nav__icon">
            </a>
        </div>

        <div class="attendance-list-card">
            <div class="attendance-list-table__header">
                <div class="col col--date">名前</div>
                <div class="col col--time">出勤</div>
                <div class="col col--time">退勤</div>
                <div class="col col--time">休憩</div>
                <div class="col col--time">合計</div>
                <div class="col col--detail">詳細</div>
            </div>

            @forelse ($attendances as $attendance)
                <div class="attendance-list-table__row">
                    <div class="cell cell--date">{{ optional($attendance->user)->name ?? '' }}</div>

                    <div class="cell cell--time">
                        {{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}
                    </div>

                    <div class="cell cell--time">
                        {{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}
                    </div>

                    <div class="cell cell--time">{{ $attendance->break_time_for_display ?? '' }}</div>

                    <div class="cell cell--time">{{ $attendance->work_time_for_display ?? '' }}</div>

                    <div class="cell cell--detail">
                        <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                    </div>
                </div>
            @empty
                <p class="attendance-list-empty">勤怠データがありません。</p>
            @endforelse
        </div>

    </div>
</div>

@endsection
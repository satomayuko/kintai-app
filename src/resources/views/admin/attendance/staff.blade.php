@extends('layouts.default')

@section('title', 'スタッフ別勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/attendance/staff.css') }}">
@endsection

@section('content')
@include('components.header')

@php
    use Carbon\Carbon;

    $monthParam = request('month');
    $month = $monthParam
        ? Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth()
        : Carbon::now()->startOfMonth();

    $prevMonth = $month->copy()->subMonth()->format('Y-m');
    $nextMonth = $month->copy()->addMonth()->format('Y-m');

    $weeks = ['日', '月', '火', '水', '木', '金', '土'];

    $attendances = $attendances ?? collect();
    $attByDate = $attendances->keyBy(function ($a) {
        $date = $a->work_date ?? $a->date ?? null;
        return $date ? Carbon::parse($date)->toDateString() : null;
    })->filter();

    $fmtTime = function ($t) {
        if (! $t) return '';
        return Carbon::parse($t)->format('H:i');
    };

    $toHM = function (?int $minutes) {
        if ($minutes === null) return '';
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;
        return $h . ':' . str_pad((string)$m, 2, '0', STR_PAD_LEFT);
    };
@endphp

<div class="admin-attendance-staff">
    <div class="admin-attendance-staff__inner">

        <div class="admin-attendance-staff__title-area">
            <span class="admin-attendance-staff__title-bar"></span>
            <h1 class="admin-attendance-staff__title">{{ $staff->name }}さんの勤怠</h1>
        </div>

        <div class="admin-attendance-staff__month-nav">
            <a class="admin-attendance-staff__month-link" href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $prevMonth]) }}">
                <img class="admin-attendance-staff__arrow" src="{{ asset('img/icon-arrow-left.png') }}" alt="前月">
                <span class="admin-attendance-staff__month-text">前月</span>
            </a>

            <div class="admin-attendance-staff__month-center">
                <img class="admin-attendance-staff__calendar" src="{{ asset('img/icon-calendar.png') }}" alt="">
                <span class="admin-attendance-staff__month">{{ $month->format('Y/m') }}</span>
            </div>

            <a class="admin-attendance-staff__month-link admin-attendance-staff__month-link--right" href="{{ route('admin.attendance.staff', ['id' => $staff->id, 'month' => $nextMonth]) }}">
                <span class="admin-attendance-staff__month-text">翌月</span>
                <img class="admin-attendance-staff__arrow" src="{{ asset('img/icon-arrow-right.png') }}" alt="翌月">
            </a>
        </div>

        <div class="admin-attendance-staff__card">
            <table class="admin-attendance-staff__table">
                <thead>
                    <tr class="admin-attendance-staff__thead-row">
                        <th class="admin-attendance-staff__th">日付</th>
                        <th class="admin-attendance-staff__th">出勤</th>
                        <th class="admin-attendance-staff__th">退勤</th>
                        <th class="admin-attendance-staff__th">休憩</th>
                        <th class="admin-attendance-staff__th">合計</th>
                        <th class="admin-attendance-staff__th">詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @for ($d = $month->copy(); $d->lte($month->copy()->endOfMonth()); $d->addDay())
                        @php
                            $dateKey = $d->toDateString();
                            $a = $attByDate->get($dateKey);

                            $start = $a ? $fmtTime($a->start_time) : '';
                            $end   = $a ? $fmtTime($a->end_time) : '';

                            $breakMinutes = null;
                            $workMinutes = null;

                            if ($a) {
                                $breakMinutes = 0;

                                $breaks = $a->breaks ?? collect();
                                foreach ($breaks as $b) {
                                    $bs = $b->break_start ?? $b->start_time ?? null;
                                    $be = $b->break_end ?? $b->end_time ?? null;

                                    if ($bs && $be) {
                                        $bsC = Carbon::parse($dateKey . ' ' . Carbon::parse($bs)->format('H:i:s'));
                                        $beC = Carbon::parse($dateKey . ' ' . Carbon::parse($be)->format('H:i:s'));
                                        $breakMinutes += $bsC->diffInMinutes($beC);
                                    }
                                }

                                if (! $a->start_time || ! $a->end_time) {
                                    $breakMinutes = null;
                                }

                                if ($a->start_time && $a->end_time) {
                                    $st = Carbon::parse($dateKey . ' ' . Carbon::parse($a->start_time)->format('H:i:s'));
                                    $en = Carbon::parse($dateKey . ' ' . Carbon::parse($a->end_time)->format('H:i:s'));
                                    $total = $st->diffInMinutes($en);
                                    $workMinutes = max(0, $total - ($breakMinutes ?? 0));
                                }
                            }

                            $dateLabel = $d->format('m/d') . '(' . $weeks[(int)$d->dayOfWeek] . ')';
                        @endphp

                        <tr class="admin-attendance-staff__tr">
                            <td class="admin-attendance-staff__td">{{ $dateLabel }}</td>
                            <td class="admin-attendance-staff__td">{{ $start }}</td>
                            <td class="admin-attendance-staff__td">{{ $end }}</td>
                            <td class="admin-attendance-staff__td">{{ $toHM($breakMinutes) }}</td>
                            <td class="admin-attendance-staff__td">{{ $toHM($workMinutes) }}</td>
                            <td class="admin-attendance-staff__td">
                                @if ($a)
                                    <a class="admin-attendance-staff__detail-link" href="{{ route('admin.attendance.detail', ['id' => $a->id]) }}">詳細</a>
                                @else
                                    <span class="admin-attendance-staff__detail-empty"></span>
                                @endif
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        <div class="admin-attendance-staff__footer">
            <a class="admin-attendance-staff__csv-button" href="{{ route('admin.attendance.export', ['id' => $staff->id, 'month' => $month->format('Y-m')]) }}">
                CSV出力
            </a>
        </div>

    </div>
</div>
@endsection
@extends('layouts.default')

@section('title', '勤怠詳細')

@section('css')
    <link rel="stylesheet" href="{{ asset('/css/attendance/detail.css') }}">
@endsection

@section('content')
    @include('components.header')

    @php
        use Carbon\Carbon;

        $fmt = function ($time) {
            return $time ? Carbon::parse($time)->format('H:i') : '';
        };

        $attendance = $attendance ?? null;
        $user = $user ?? null;

        $statusRaw = $latestRequest->status ?? null;
        $status = null;

        if ($statusRaw !== null) {
            if (is_numeric($statusRaw)) {
                $status = (int) $statusRaw;
            } else {
                $s = (string) $statusRaw;
                if ($s === '承認待ち') $status = 0;
                elseif ($s === '承認済み') $status = 1;
                elseif ($s === '却下' || $s === '否認' || $s === '否認済み') $status = 2;
                else $status = -1;
            }
        }

        $hasRequest = !empty($latestRequest) && $status !== null;

        $isPending  = $hasRequest && $status === 0;
        $isApproved = $hasRequest && $status === 1;
        $isRejected = $hasRequest && $status === 2;

        $canEdit = !$hasRequest || $isRejected;

        $displayStart = $fmt($hasRequest ? ($latestRequest->corrected_start ?? $attendance->start_time) : $attendance->start_time);
        $displayEnd   = $fmt($hasRequest ? ($latestRequest->corrected_end ?? $attendance->end_time) : $attendance->end_time);

        $displayRemark = $hasRequest ? ($latestRequest->remark ?? '') : ($attendance->remark ?? '');

        $vStart  = old('start_time', $displayStart);
        $vEnd    = old('end_time', $displayEnd);
        $vRemark = old('remark', $displayRemark);

        $attendanceBreaks = $breaks ?? collect();

        $requestBreaks = collect();
        if ($hasRequest && $latestRequest) {
            if ($latestRequest->relationLoaded('breaks')) {
                $requestBreaks = $latestRequest->breaks ?? collect();
            } elseif (method_exists($latestRequest, 'breaks')) {
                try {
                    $requestBreaks = $latestRequest->breaks()->orderBy('sort_order')->get();
                } catch (\Throwable $e) {
                    $requestBreaks = collect();
                }
            }
        }

        $displayBreaks = ($hasRequest && $requestBreaks->count() > 0) ? $requestBreaks : $attendanceBreaks;

        $maxSlots = 6;

        $oldBreaks = old('breaks');
        $rows = [];

        if (is_array($oldBreaks)) {
            foreach ($oldBreaks as $row) {
                $rows[] = [
                    'break_start' => $row['break_start'] ?? '',
                    'break_end'   => $row['break_end'] ?? '',
                ];
            }
        } else {
            foreach ($displayBreaks as $b) {
                $rows[] = [
                    'break_start' => $fmt($b->break_start ?? null),
                    'break_end'   => $fmt($b->break_end ?? null),
                ];
            }
        }

        $filledCount = 0;
        foreach ($rows as $r) {
            if (($r['break_start'] ?? '') !== '' || ($r['break_end'] ?? '') !== '') {
                $filledCount++;
            }
        }

        $initialVisible = $filledCount > 0 ? min($filledCount + 1, $maxSlots) : 2;

        $formBreaks = [];
        for ($i = 0; $i < $maxSlots; $i++) {
            $formBreaks[] = $rows[$i] ?? ['break_start' => '', 'break_end' => ''];
        }

        $readonlyBreaks = $displayBreaks->values();
        $readonlyCount = $readonlyBreaks->count();
        $readonlyShowCount = $readonlyCount > 0 ? $readonlyCount : 2;

        $labelFor = function (int $i) {
            return $i === 0 ? '休憩' : '休憩' . ($i + 1);
        };

        $nbsp = "\u{00A0}";
    @endphp

    <div class="attendance-detail-page">
        <div class="attendance-detail-inner">

            <div class="attendance-detail-title">
                <span class="attendance-detail-title-line"></span>
                <span class="attendance-detail-title-text">勤怠詳細</span>
            </div>

            @if (!$canEdit)

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
                            @php
                                $hasSE = ($displayStart !== '') || ($displayEnd !== '');
                            @endphp
                            @if ($hasSE)
                                <span class="time-text">{{ $displayStart !== '' ? $displayStart : $nbsp }}</span>
                                <span class="time-separator">〜</span>
                                <span class="time-text">{{ $displayEnd !== '' ? $displayEnd : $nbsp }}</span>
                            @else
                                <span class="time-text">{{ $nbsp }}</span>
                                <span class="time-text">{{ $nbsp }}</span>
                                <span class="time-text">{{ $nbsp }}</span>
                            @endif
                        </div>
                    </div>

                    @for ($i = 0; $i < $readonlyShowCount; $i++)
                        @php
                            $b = $readonlyBreaks->get($i);
                            $bs = $b ? $fmt($b->break_start ?? null) : '';
                            $be = $b ? $fmt($b->break_end ?? null) : '';
                            $has = ($bs !== '') || ($be !== '');
                        @endphp
                        <div class="attendance-detail-row">
                            <div class="attendance-detail-label">{{ $labelFor($i) }}</div>
                            <div class="attendance-detail-value attendance-detail-value--time-range">
                                @if ($has)
                                    <span class="time-text">{{ $bs !== '' ? $bs : $nbsp }}</span>
                                    <span class="time-separator">〜</span>
                                    <span class="time-text">{{ $be !== '' ? $be : $nbsp }}</span>
                                @else
                                    <span class="time-text attendance-detail-placeholder" aria-hidden="true">{{ $nbsp }}</span>
                                    <span class="time-separator attendance-detail-placeholder" aria-hidden="true">{{ $nbsp }}</span>
                                    <span class="time-text attendance-detail-placeholder" aria-hidden="true">{{ $nbsp }}</span>
                                @endif
                            </div>
                        </div>
                    @endfor

                    <div class="attendance-detail-row attendance-detail-row--remark">
                        <div class="attendance-detail-label">備考</div>
                        <div class="attendance-detail-value">
                            <div class="attendance-detail-remark-text">
                                {{ $displayRemark }}
                            </div>
                        </div>
                    </div>

                </div>

                @if ($isPending)
                    <p class="attendance-detail-warning">*承認待ちのため修正はできません。</p>
                @elseif ($isApproved)
                    <p class="attendance-detail-warning">*承認済みのため修正はできません。</p>
                @endif

            @else

                <form action="{{ route('stamp_correction_request.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

                    @if ($errors->any())
                        <ul class="attendance-detail-errors">
                            @foreach ($errors->all() as $error)
                                <li class="attendance-detail-error">{{ $error }}</li>
                            @endforeach
                        </ul>
                    @endif

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

                        @foreach ($formBreaks as $i => $row)
                            @php
                                $visible = ($i + 1) <= $initialVisible;
                                $style = $visible ? '' : 'display:none;';
                                $bsVal = $row['break_start'] ?? '';
                                $beVal = $row['break_end'] ?? '';
                            @endphp
                            <div class="attendance-detail-row js-break-row" data-break-index="{{ $i }}" style="{{ $style }}">
                                <div class="attendance-detail-label">{{ $labelFor($i) }}</div>
                                <div class="attendance-detail-value attendance-detail-value--time-range">
                                    <span class="time-box">
                                        <input type="time"
                                               name="breaks[{{ $i }}][break_start]"
                                               value="{{ $bsVal }}"
                                               class="attendance-detail-input attendance-detail-input--time js-break-input {{ $bsVal === '' ? 'is-empty' : '' }}"
                                               step="60">
                                    </span>
                                    <span class="time-separator">〜</span>
                                    <span class="time-box">
                                        <input type="time"
                                               name="breaks[{{ $i }}][break_end]"
                                               value="{{ $beVal }}"
                                               class="attendance-detail-input attendance-detail-input--time js-break-input {{ $beVal === '' ? 'is-empty' : '' }}"
                                               step="60">
                                    </span>
                                </div>
                            </div>
                        @endforeach

                        <div class="attendance-detail-row attendance-detail-row--remark">
                            <div class="attendance-detail-label">備考</div>
                            <div class="attendance-detail-value">
                                <div class="attendance-detail-remark-box">
                                    <textarea name="remark" class="attendance-detail-textarea" maxlength="255">{{ $vRemark }}</textarea>
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

            const syncEmpty = (el) => {
                if (el.value && el.value.trim() !== '') {
                    el.classList.remove('is-empty');
                } else {
                    el.classList.add('is-empty');
                }
            };

            timeInputs.forEach((el) => {
                syncEmpty(el);
                el.addEventListener('input', () => syncEmpty(el));
                el.addEventListener('change', () => syncEmpty(el));
                el.addEventListener('blur', () => syncEmpty(el));
            });

            const rows = Array.from(document.querySelectorAll('.js-break-row'));
            const inputs = Array.from(document.querySelectorAll('.js-break-input'));

            const rowHasValue = (row) => {
                const ins = row.querySelectorAll('input[type="time"]');
                for (const i of ins) {
                    if (i.value && i.value.trim() !== '') return true;
                }
                return false;
            };

            const revealUpTo = (index) => {
                rows.forEach((r) => {
                    const i = parseInt(r.dataset.breakIndex || '0', 10);
                    if (i <= index) r.style.display = '';
                });
            };

            let lastFilled = -1;
            rows.forEach((r) => {
                const i = parseInt(r.dataset.breakIndex || '0', 10);
                if (rowHasValue(r)) lastFilled = Math.max(lastFilled, i);
            });

            if (lastFilled >= 0) {
                revealUpTo(Math.min(lastFilled + 1, rows.length - 1));
            }

            const ensureNextRow = () => {
                let maxVisible = -1;
                rows.forEach((r) => {
                    const i = parseInt(r.dataset.breakIndex || '0', 10);
                    if (r.style.display !== 'none') maxVisible = Math.max(maxVisible, i);
                });

                if (maxVisible < 0) return;

                const visibleRows = rows.filter(r => r.style.display !== 'none');
                const lastRow = visibleRows[visibleRows.length - 1];

                if (lastRow && rowHasValue(lastRow)) {
                    const nextIndex = maxVisible + 1;
                    if (rows[nextIndex]) {
                        rows[nextIndex].style.display = '';
                    }
                }
            };

            inputs.forEach((el) => {
                el.addEventListener('input', ensureNextRow);
                el.addEventListener('change', ensureNextRow);
                el.addEventListener('blur', ensureNextRow);
            });
        });
    </script>
@endsection
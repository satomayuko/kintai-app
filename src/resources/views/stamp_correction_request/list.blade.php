@extends('layouts.default')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request/list.css') }}">
@endsection

@section('content')
@include('components.header')

@php
    $currentStatus = $status ?? request('status', 'pending');
@endphp

<div class="request-list-page">
    <div class="request-list-container">

        <div class="request-list-title">
            <span class="request-list-title-bar"></span>
            <h1 class="request-list-title-text">申請一覧</h1>
        </div>

        <div class="request-list-tabs">
            <a href="{{ route('stamp_correction_request.list', ['status' => 'pending']) }}"
               class="request-list-tab {{ $currentStatus === 'pending' ? 'is-active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('stamp_correction_request.list', ['status' => 'approved']) }}"
               class="request-list-tab {{ $currentStatus === 'approved' ? 'is-active' : '' }}">
                承認済み
            </a>
        </div>
        <div class="request-list-tabs-line"></div>

        <div class="request-list-table-wrap">
            <table class="request-list-table">
                <thead>
                    <tr>
                        <th class="request-list-th">状態</th>
                        <th class="request-list-th">名前</th>
                        <th class="request-list-th">対象日時</th>
                        <th class="request-list-th">申請理由</th>
                        <th class="request-list-th">申請日時</th>
                        <th class="request-list-th">詳細</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($requests as $req)
                        @php
                            $statusLabel = $req->status_label
                                ?? (($req->status ?? null) === 'approved' || ($req->status ?? null) === 1 ? '承認済み' : '承認待ち');

                            $userName = $req->user->name ?? $req->name ?? '';

                            $targetRaw = $req->attendance->work_date
                                ?? $req->attendance->date
                                ?? $req->target_date
                                ?? $req->attendance_date
                                ?? null;

                            $targetDate = $targetRaw ? \Illuminate\Support\Carbon::parse($targetRaw)->format('Y/m/d') : '';

                            $reason = $req->remark ?? $req->reason ?? '';

                            $appliedAt = $req->created_at ? \Illuminate\Support\Carbon::parse($req->created_at)->format('Y/m/d') : '';
                        @endphp

                        <tr class="request-list-tr">
                            <td class="request-list-td request-list-td--status">{{ $statusLabel }}</td>
                            <td class="request-list-td request-list-td--name">{{ $userName }}</td>
                            <td class="request-list-td request-list-td--date">{{ $targetDate }}</td>
                            <td class="request-list-td request-list-td--reason">{{ $reason }}</td>
                            <td class="request-list-td request-list-td--date">{{ $appliedAt }}</td>
                            <td class="request-list-td request-list-td--detail">
                                <a href="{{ route('stamp_correction_request.show', $req->id) }}" class="request-list-detail-link">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr class="request-list-tr">
                            <td class="request-list-td request-list-td--empty" colspan="6">該当する申請がありません</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
@extends('layouts.default')

@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('/css/admin/staff/list.css') }}">
@endsection

@section('content')
@include('components.header')

<div class="admin-staff-list">
    <div class="admin-staff-list__inner">
        <div class="admin-staff-list__title-area">
            <span class="admin-staff-list__title-bar"></span>
            <h1 class="admin-staff-list__title">スタッフ一覧</h1>
        </div>

        <div class="admin-staff-list__card">
            <table class="admin-staff-list__table">
                <thead>
                    <tr class="admin-staff-list__thead-row">
                        <th class="admin-staff-list__th">名前</th>
                        <th class="admin-staff-list__th">メールアドレス</th>
                        <th class="admin-staff-list__th">月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($staffs as $staff)
                        <tr class="admin-staff-list__tr">
                            <td class="admin-staff-list__td">{{ $staff->name }}</td>
                            <td class="admin-staff-list__td">{{ $staff->email }}</td>
                            <td class="admin-staff-list__td">
                                <a class="admin-staff-list__detail-link" href="{{ route('admin.attendance.staff', ['id' => $staff->id]) }}">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
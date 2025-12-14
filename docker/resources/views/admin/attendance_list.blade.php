@extends('layouts.admin_app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-list">
    <div class="attendance-list__title">{{ $targetDate->format('Y年n月j日') }}の勤怠</div>

    <div class="attendance-list__nav">
        <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}">← 前日</a>
        <span class="date-display">{{ $targetDate->format('Y/m/d') }}</span>
        <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">翌日 →</a>
    </div>

    <table class="attendance-table">
        <tr>
            <th>名前</th>
            <th>出勤</th>
            <th>退勤</th>
            <th>休憩</th>
            <th>合計</th>
            <th>詳細</th>
        </tr>
        <tr>
            <td>山田 太郎</td>
            <td>09:00</td>
            <td>18:00</td>
            <td>1:00</td>
            <td>8:00</td>
            <td><a href="#">詳細</a></td>
        </tr>
    </table>
</div>
@endsection

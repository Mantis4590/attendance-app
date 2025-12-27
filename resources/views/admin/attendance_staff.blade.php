@extends('layouts.admin_app')

@section('title', 'スタッフ別勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_staff.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endsection

@section('content')
<main class="attendance-staff">
    <div class="attendance-staff__title">
        {{ $staff->name }}さんの勤怠
    </div>

    {{-- 月ナビ --}}
    <div class="attendance-staff__nav">
        <a href="?month={{ $month->copy()->subMonth()->format('Y-m') }}" class="attendance-list__nav-link attendance-list__nav-link--prev">← 前月</a>
        <div class="attendance-staff__nav-center">
           <svg class="attendance-staff__icon" viewBox="0 0 24 24">
                <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .89-2 
                2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5c0-1.11-.89-2-2-2m0 
                16H5V9h14v10M5 7V5h14v2H5m4 6h2v2H9v-2m4 
                0h2v2h-2v-2Z"/>
            </svg>

            {{ $month->format('Y/m') }}
        </div>
        <a href="?month={{ $month->copy()->addMonth()->format('Y-m') }}" class="attendance-list__nav-link attendance-list__nav-link--next">翌月 →</a>
    </div>

    <table class="attendance-staff__table">
        <thead class="t__head">
            <tr>
                <th class="staff-th">日付</th>
                <th class="staff-th">出勤</th>
                <th class="staff-th">退勤</th>
                <th class="staff-th">休憩</th>
                <th class="staff-th">合計</th>
                <th class="staff-th">詳細</th>
            </tr>
        </thead>
        <tbody>
        @foreach ($days as $day)
            @php
                $date = $day['date'];                 // ← Carbon
                $attendance = $day['attendance'];     // ← Attendance|null
                $weekMap = ['日','月','火','水','木','金','土'];
            @endphp

            <tr>
                {{-- 日付 --}}
                <td class="staff-td">
                    {{ $date->format('m/d') }}
                    ({{ $weekMap[$date->dayOfWeek] }})
                </td>

                @if ($attendance)
                    <td class="staff-td">{{ optional($attendance->clock_in)->format('H:i') ?? '-' }}</td>
                    <td class="staff-td">{{ optional($attendance->clock_out)->format('H:i') ?? '-' }}</td>
                    <td class="staff-td">{{ $attendance->total_break_display ?? '-' }}</td>
                    <td class="staff-td">{{ $attendance->total_work_display ?? '-' }}</td>
                    <td class="staff-td">
                        <a href="{{ route('admin.attendance.show', $attendance->id) }}" class="detail-btn">詳細</a>
                    </td>
                @else
                    <td class="staff-td"></td>
                    <td class="staff-td"></td>
                    <td class="staff-td"></td>
                    <td class="staff-td"></td>
                    <td class="staff-td text-muted"></td>
                @endif
            </tr>
        @endforeach

        </tbody>
    </table>

    <div class="attendance-staff__footer">
        <a href="{{ route('admin.attendance.staff.csv', [
            'id' => $staff->id,
            'month' => $month->format('Y-m'),
            ]) }}" class="attendance-staff__csv-btn">
            CSV出力
        </a>
    </div>
</main>
@endsection
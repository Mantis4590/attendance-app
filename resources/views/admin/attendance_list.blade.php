@extends('layouts.admin_app')

@section('title', '勤怠一覧(管理者)')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance__list.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
@endsection

@section('content')
<main class="attendance__list">
    <div class="attendance__list-title">{{ $targetDate->format('Y年n月j日')}}の勤怠</div>

    {{-- 日付ナビ (前日 / 当日 / 翌日) --}}

    <div class="attendance-list__nav">
        <a href="{{ route('admin.attendance.list', ['date' => $prevDate->format('Y-m-d')]) }}" class="attendance-list__nav-link attendance-list__nav-link--prev">
            ← 前日
        </a>

        <div class="attendance-list__nav-center">
           <svg class="attendance-list__icon" viewBox="0 0 24 24">
                <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .89-2 
                2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5c0-1.11-.89-2-2-2m0 
                16H5V9h14v10M5 7V5h14v2H5m4 6h2v2H9v-2m4 
                0h2v2h-2v-2Z"/>
            </svg>

            {{ $targetDate->format('Y/m/d') }}
        </div>

        <a href="{{ route('admin.attendance.list', ['date' => $nextDate->format('Y-m-d')]) }}" class="attendance-list__nav-link attendance-list__nav-link--next">
            翌日 →
        </a>
    </div>

    {{-- 勤怠テーブル --}}
    <table class="attendance-list__table">
        <thead class="t__head">
            <tr class="t__head-tr">
                <th class="attendance-list__th">名前</th>
                <th class="attendance-list__th">出勤</th>
                <th class="attendance-list__th">退勤</th>
                <th class="attendance-list__th">休憩</th>
                <th class="attendance-list__th">合計</th>
                <th class="attendance-list__th">詳細</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($attendances as $attendance)
                <tr class="t__body-tr">
                    <td class="attendance-list__td">
                        {{ $attendance->user->name }}
                    </td>
                    <td class="attendance-list__td">
                        {{ optional($attendance->clock_in)->format('H:i') ?? '-' }}
                    </td>
                    <td class="attendance-list__td">
                        {{ optional($attendance->clock_out)->format('H:i') ?? '-' }}
                    </td>
                    <td class="attendance-list__td">
                        {{ $attendance->total_break_display ?? '-' }}
                    </td>
                    <td class="attendance-list__td">
                        {{ $attendance->total_work_display ?? '-' }}
                    </td>
                    <td class="attendance-list__td attendance-list__td--link">
                        {{-- ルート名は仮置き --}}
                        <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}" class="attendance-list__detail-link">
                            詳細
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="attendance-list__empty" colspan="6">
                        この日の勤怠データはありません
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</main>
@endsection
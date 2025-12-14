@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

@endsection

@section('content')
<main class="attendance-list">

    {{-- タイトル --}}
    <div class="attendance-list__title">勤怠一覧</div>

    {{-- 月移動 --}}
    <div class="attendance-list__month-nav">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="attendance-list__prev">← 前月</a>

        <div class="attendance-list__current-month">
            <svg class="attendance-list__icon" viewBox="0 0 24 24">
                <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .89-2 
                2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5c0-1.11-.89-2-2-2m0 
                16H5V9h14v10M5 7V5h14v2H5m4 6h2v2H9v-2m4 
                0h2v2h-2v-2Z"/>
            </svg>

            {{ $targetMonth->format('Y/m') }}
        </div>



        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="attendance-list__next">翌月 →</a>
    </div>

    {{-- テーブル --}}
    <table class="attendance-list__table">
        <thead class="t__head">
            <tr class="t__head-tr">
                <th class="t__head-th">日付</th>
                <th class="t__head-th">出勤</th>
                <th class="t__head-th">退勤</th>
                <th class="t__head-th">休憩</th>
                <th class="t__head-th">合計</th>
                <th class="t__head-th">詳細</th>
            </tr>
        </thead>
            
        <tbody>
        @foreach($attendances as $attendance)
            <tr>
                @php
                    $weekMap = ['Mon'=>'月','Tue'=>'火','Wed'=>'水','Thu'=>'木','Fri'=>'金','Sat'=>'土','Sun'=>'日'];
                @endphp

                <td class="t__body-td">
                    {{ $attendance->date->format('m/d') }}
                    ({{ $weekMap[$attendance->date->format('D')] }})
                </td>


                {{-- 出勤 --}}
                <td class="t__body-td">{{ optional($attendance->clock_in)->format('H:i') ?? '-' }}</td>

                {{-- 退勤 --}}
                <td class="t__body-td">{{ optional($attendance->clock_out)->format('H:i') ?? '-' }}</td>

                {{-- 休憩（合計） --}}
                <td class="t__body-td">{{ $attendance->total_break ? substr($attendance->total_break, 0, 5) : '-' }}</td>

                {{-- 合計（勤務時間） --}}
                <td class="t__body-td">{{ $attendance->total_work ? substr($attendance->total_work, 0, 5) : '-' }}</td>

                <td>
                    <a href="{{ route('attendance.detail', ['id' => $attendance->id]) }}" class="detail-btn">詳細</a>
                </td>
            </tr>
        @endforeach
        </tbody>

    </table>

</main>
@endsection

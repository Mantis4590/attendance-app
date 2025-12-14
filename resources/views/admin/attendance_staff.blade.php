@extends('layouts.admin_app')

@section('title', 'スタッフ別勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_staff.css') }}">
@endsection

@section('content')
<main class="attendance-staff">
    <div class="attendance-staff__title">
        {{ $staff->name }}さんの勤怠
    </div>

    {{-- 月ナビ --}}
    <div class="attendance-staff__nav">
        <a href="?month={{ $month->copy()->subMonth()->format('Y-m') }}">← 前月</a>
        <span>{{ $month->format('Y/m') }}</span>
        <a href="?month={{ $month->copy()->addMonth()->format('Y-m') }}">翌月 →</a>
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
            @forelse ($days as $day)
                <tr>
                    <td class="staff-td">{{ $day['date']->format('m/d(D)') }}</td>
                    <td class="staff-td">{{ optional($day['attendance']?->clock_in)->format('H:i') }}</td>
                    <td class="staff-td">{{ optional($day['attendance']?->clock_out)->format('H:i') }}</td>
                    <td class="staff-td">
                    {{ $day['attendance']->total_break_display ?? '' }}
                    </td>
                    <td class="staff-td">{{ $day['attendance']->total_work_display ?? '' }}</td>
                    <td class="staff-td">
                        @if ($day['attendance'])
                            <a href="{{ route('admin.attendance.show', $day['attendance']->id) }}">詳細</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">勤怠データがありません</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="attendance-staff__footer">
        <button class="attendance-staff__csv-btn">CSV出力</button>
    </div>
</main>
@endsection
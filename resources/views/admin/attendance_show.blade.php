@extends('layouts.admin_app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-attendance-show.css') }}">
@endsection

@section('content')
<main class="attendance-show">

    <h2 class="attendance-show__title">
        勤怠詳細
    </h2>
    <div class="attendance-show__card">
        
        {{-- 名前 --}}
        <div class="attendance-show__row">
            <div class="attendance-show__label">名前</div>
            <div class="attendance-show__value">{{ $attendance->user->name }}</div>
        </div>

        {{-- 日付 --}}
        <div class="attendance-show__row">
            <div class="attendance-show__label">日付</div>
            <div class="attendance-show__value attendance-show__date">
                <span class="attendance-show__year">{{ $attendance->date->format('Y年') }}</span>
                <span class="attendance-show__day">{{ $attendance->date->format('n月j日') }}</span>
            </div>
        </div>

        {{-- 出勤・退勤 --}}
        <div class="attendance-show__row">
            <div class="attendance-show__label">出勤</div>
            <div class="attendance-show__value attendance-show__value--time">
                <input type="time" value="{{ optional($attendance->clock_in)->format('H:i') }}" disabled>
                〜
                <input type="time" value="{{ optional($attendance->clock_out)->format('H:i') }}" disabled>
            </div>
        </div>

        {{-- 既存の休憩 --}}
        @foreach ($attendance->breakTimes as $index => $break)
            <div class="attendance-show__row">
                <div class="attendance-show__label">
                    休憩{{ $index + 1 }}
                </div>
                <div class="attendance-show__value attendance-show__value--time">
                    <input type="time"
                    value="{{ optional($break->break_start)->format('H:i') }}"
                    disabled>
                    〜
                    <input type="time"
                    value="{{ optional($break->break_end)->format('H:i') }}"
                    disabled>
                </div>
            </div>
        @endforeach

        {{-- 追加用の空フィールド --}}
        <div class="attendance-show__row">
            <div class="attendance-show__label">
                休憩{{ $attendance->breakTimes->count() + 1 }}
            </div>
             <div class="attendance-show__value attendance-show__value--time">
                <input type="time" value="" disabled>
                〜
                <input type="time" value="" disabled>
            </div>
        </div>

        {{-- 備考 --}}
        <div class="attendance-show__row">
            <div class="attendance-show__label">備考</div>
            <div class="attendance-show__value">
                <textarea name="note" class="note-input">{{ old('note', $attendance->note) }}</textarea>

            </div>
        </div>

        {{-- 修正ボタン --}}
        <div class="attendance-show__footer">
            <button type="submit" class="attendance-show__edit-btn">修正</button>
        </div>

    </div>
</main>
@endsection
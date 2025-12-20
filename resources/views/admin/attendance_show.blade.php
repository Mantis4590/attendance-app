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

    <form method="POST" action="{{ route('admin.attendance.update', $attendance->id) }}">
        @csrf
        @method('PATCH')
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
                    <input type="text" name="clock_in" value="{{ old('clock_in', $attendance->clock_in?->format('H:i')) }}" class="time-input">
                    〜
                    <input type="text" name="clock_out" value="{{ old('clock_out', $attendance->clock_out?->format('H:i')) }}" class="time-input">

                    {{-- 出勤・退勤エラー --}}
                    @error('clock_in')
                        <p class="attendance-show__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 既存の休憩 --}}
            @foreach ($attendance->breakTimes as $index => $break)
                <div class="attendance-show__row">
                    <div class="attendance-show__label">
                        休憩{{ $index + 1 }}
                    </div>
                    <div class="attendance-show__value attendance-show__value--time">
                        <input type="text" name="breaks[{{ $index }}][start]" value="{{ old("breaks.$index.start", $break->break_start?->format('H:i')) }}" class="time-input">
                        〜
                        <input type="text" name="breaks[{{ $index }}][end]" value="{{ old("breaks.$index.end", $break->break_end?->format('H:i')) }}" class="time-input">
                    </div>
                </div>
            @endforeach

            {{-- 休憩エラー（まとめて1回） --}}
            @error('breaks')
                <div class="attendance-show__row">
                    <div class="attendance-show__label"></div>
                    <div class="attendance-show__value">
                        <p class="attendance-show__error">{{ $message }}</p>
                    </div>
                </div>
            @enderror

            {{-- 追加用の空フィールド --}}
            <div class="attendance-show__row">
                <div class="attendance-show__label">
                    休憩{{ $attendance->breakTimes->count() + 1 }}
                </div>
                <div class="attendance-show__value attendance-show__value--time">
                    <input type="text" name="breaks[new][start]" value="{{ old('breaks.new.start') }}" class="time-input">
                    〜
                    <input type="text" name="breaks[new][end]" value="{{ old('breaks.new.end') }}" class="time-input">
                </div>
            </div>

            {{-- 備考 --}}
            <div class="attendance-show__row">
                <div class="attendance-show__label">備考</div>
                <div class="attendance-show__value">
                    <textarea name="note" class="note-input">{{ old('note', $attendance->note) }}</textarea>

                    @error('note')
                        <p class="attendance-show__error">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 修正ボタン --}}
            <div class="attendance-show__footer">
                @if ($hasPendingRequest)
                    <p class="attendance-show__message">
                        ※ 承認待ちのため修正はできません
                    </p>
                @else
                    <button type="submit" class="attendance-show__edit-btn">
                        修正
                    </button>
                @endif
            </div>
        </div>
    </form>
</main>
@endsection
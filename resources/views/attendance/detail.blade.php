@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<main class="attendance-detail">
    <div class="attendance-detail__title">勤怠詳細</div>

    {{-- 修正フォーム --}}
    <form action="{{ route('attendance.request', $attendance->id) }}" method="POST">
        @csrf

        <div class="attendance-detail__box">

            {{-- 名前（編集不可）--}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">名前</div>
                <div class="attendance-detail__value">{{ $user->name }}</div>
            </div>

            {{-- 日付（編集不可）--}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">日付</div>
                <div class="attendance-detail__value attendance-detail__date">
                    <span class="attendance-detail__year">{{ $attendance->date->format('Y年') }}</span>
                    <span class="attendance-detail__day">{{ $attendance->date->format('n月j日') }}</span>
                </div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">出勤・退勤</div>
                <div class="attendance-detail__value attendance-detail__value--time">
                    <input type="text" name="clock_in" value="{{ old('clock_in', $attendance->clock_in?->format('H:i')) }}" class="time-input">
                    〜
                    <input type="text" name="clock_out" value="{{ old('clock_out', $attendance->clock_out?->format('H:i')) }}" class="time-input">
                </div>
            </div>

            {{-- エラー表示 --}}
            @if ($errors->has('clock_in') || $errors->has('clock_out'))
                <p class="input-error">
                    {{ $errors->first('clock_in') ?: $errors->first('clock_out') }}
                </p>
            @endif

            {{-- 休憩 --}}
            @foreach ($attendance->breakTimes as $index => $break)
                <div class="attendance-detail__row">
                    <div class="attendance-detail__label">休憩{{ $index + 1 }}</div>
                    <div class="attendance-detail__value attendance-detail__value--time">
                        <input type="text" name="breaks[{{ $index }}][start]" value="{{ old("breaks.$index.start", $break->break_start?->format('H:i')) }}" class="time-input">
                        〜
                        <input type="text" name="breaks[{{ $index }}][end]" value="{{ old("breaks.$index.end", $break->break_end?->format('H:i')) }}" class="time-input">
                    </div>
                </div>

                {{-- エラー表示 --}}
                @if ($errors->has("breaks.$index.start") || $errors->has("breaks.$index.end"))
                    <p class="input-error">
                        {{ $errors->first("breaks.$index.start") ?: $errors->first("breaks.$index.end") }}
                    </p>
                @endif

            @endforeach

            {{-- 追加の空行（必須） --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">休憩{{ $attendance->breakTimes->count() + 1 }}</div>
                <div class="attendance-detail__value attendance-detail__value--time">
                <input type="text" name="breaks[new][start]" value="{{ old('breaks.new.start') }}" class="time-input">
                〜
                <input type="text" name="breaks[new][end]" value="{{ old('breaks.new.end') }}" class="time-input">
                </div>
            </div>

            {{-- 備考 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">備考</div>
                <div class="attendance-detail__value">
                    <textarea name="note" class="note-input">{{ old('note', $attendance->note) }}</textarea>
                </div>
            </div>

            @error('note')
                <p class="input-error">{{ $message }}</p>
            @enderror

        </div>

        {{-- 修正ボタン or メッセージ --}}
        <div class="attendance-detail__footer">

            @if($hasPendingRequest)
                <p class="attendance-detail__note">＊承認待ちのため修正はできません。</p>
            @elseif ($hasApprovedRequest)
                <p class="attendance-detail__note">※ 承認済みの勤怠は修正できません</p>
            @else
                <button type="submit" class="attendance-detail__button">修正</button>
            @endif
        </div>

    </form>

</main>
@endsection
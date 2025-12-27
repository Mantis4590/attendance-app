@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')

<main class="index">

    {{-- ステータス --}}
    <div class="index__status">
        <span class="index__status-label">{{ $status }}</span>
    </div>

    {{-- 現在時刻 --}}
    <div class="index__date">{{ $nowDate }}</div>
    <div class="index__time">{{ $nowTime }}</div>

    {{-- 状態のよるボタン出し分け --}}
    @if ($status === '勤務外')
        {{-- 出勤前 --}}
        <form action="{{ route('attendance.clockIn') }}" method="POST">
            @csrf
            <button type="submit" class="index__button-start">出勤</button>
        </form>

    @elseif ($status === '出勤中')
        {{-- 出勤後 --}}
        <div class="index__buttons">
            <form action="{{ route('attendance.clockOut') }}" method="POST">
                @csrf
                <button class="index__button index__button--black">退勤</button>
            </form>

            <form action="{{ route('attendance.startBreak') }}" method="POST">
                @csrf
                <button class="index__button index__button--white">休憩入</button>
            </form>
        </div>

    @elseif ($status === '休憩中')
        {{-- 休憩中：休憩戻 --}}
        <form action="{{ route('attendance.endBreak') }}" method="POST">
            @csrf
            <button class="index__button index__button--white">休憩戻</button>
        </form>
    @elseif ($status === '退勤済')
        {{-- 退勤後 --}}
        <p class="index__message">お疲れ様でした。</p>
    @endif
    
</main>
@endsection
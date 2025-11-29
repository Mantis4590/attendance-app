@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
<main class="attendance-detail">
    <div class="attendance-detail__title">勤怠詳細</div>

        <div class="attendance-detail__card">

            {{-- 名前 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">名前</div>
                <div class="attendance-detail__value">西 玲奈</div>
            </div>

            {{-- 日付 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">日付</div>
                <div class="attendance-detail__value">2023年 6月1日</div>
            </div>

            {{-- 出勤・退勤 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">出勤・退勤</div>
                <div class="attendance-detail__value">09:00 〜 18:00
                </div>
            </div>

            {{-- 休憩1 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">休憩</div>
                <div class="attendance-detail__value">
                12:00 〜 13:00
                </div>
            </div>

            {{-- 休憩2 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">休憩2</div>
                <div class="attendance-detail__value">
                - 〜 -
                </div>
            </div>

            {{-- 備考 --}}
            <div class="attendance-detail__row">
                <div class="attendance-detail__label">備考</div>
                <div class="attendance-detail__value">電車遅延のため</div>
            </div>
        </div>

        <div class="attendance-detail__footer">
            <button class="attendance-detail__button">修正</button>
        </div>

</main>
@endsection
@extends('layouts.admin_app')

@section('title', '修正申請承認')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request_show.css') }}">
@endsection

@section('content')
<main class="request-show">
    <div class="request-show__title">勤怠詳細</div>
    <div class="request-show__card">
        {{-- 名前 --}}
        <div class="request-show__row">
            <div class="request-show__label">名前</div>
            <div class="request-show__value">{{ $user->name }}</div>
        </div>

        {{-- 日付 --}}
        <div class="request-show__row">
            <div class="request-show__label">日付</div>
            <div class="request-show__value request-show__date">
                <span class="request-show__year">{{ $attendance->date->format('Y年') }}</span>
                <span></span>
                <span class="request-show__day">{{ $attendance->date->format('n月j日') }}</span>
            </div>
        </div>

        {{-- 出勤・退勤 --}}
        <div class="request-show__row">
            <div class="request-show__label">出勤・退勤</div>
            <div class="request-show__value">
                <span>{{ \Carbon\Carbon::parse($request->clock_in)->format('H:i') }}</span>
                <span>〜</span>
                <span>{{ \Carbon\Carbon::parse($request->clock_out)->format('H:i') }}</span>
            </div>
        </div>

        {{-- 休憩 --}}
        @if (!empty($request->breaks))
            @foreach ($request->breaks as $index => $break)
                @if (!empty($break['start']) || !empty($break['end']))
                    <div class="request-show__row">
                        <div class="request-show__label">休憩{{ $loop->iteration }}</div>
                        <div class="request-show__value">
                            <span>{{ $break['start'] ?? '-' }}</span>
                            <span>〜</span>
                            <span>{{ $break['end'] ?? '-' }}</span>
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

        {{-- 備考 --}}
        <div class="request-show__row">
            <div class="request-show__label">備考</div>
            <div class="request-show__value">
                {{ $request->note }}
            </div>
        </div>
    </div>

    {{-- 承認ボタン --}}
    <div class="request-show__footer">
        @if ($request->status === 'pending')
            <form method="POST" action="{{ route('admin.stamp_correction_request.approve', $request->id) }}">
                @csrf
                <button class="request-show__btn">
                    承認
                </button>
            </form>
        @else
            <button class="request-show__btn request-show__btn--done" disabled>
                承認済み
            </button>
        @endif
    </div>
</main>
@endsection
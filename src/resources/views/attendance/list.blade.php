@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
@endsection

@section('content')
<main class="attendance-list">

    {{-- タイトル --}}
    <div class="attendance-list__title">勤怠一覧</div>

    {{-- 月移動 --}}
    <div class="attendance-list__month-nav">
        <a href="#" class="attendance-list__prev">← 前月</a>

        <div class="attendance-list__current-month">
            2025/11
        </div>

        <a href="#" class="attendance-list__next">翌月 →</a>
    </div>

    {{-- テーブル --}}
    <table class="attendance-list__table">
        <thead class="t__head">
            <tr>
                <th class="t__head-th">日付</th>
                <th class="t__head-th">出勤</th>
                <th class="t__head-th">退勤</th>
                <th class="t__head-th">休憩</th>
                <th class="t__head-th">合計</th>
                <th class="t__head-th">詳細</th>
            </tr>
        </thead>
            
        <tbody>
            {{-- 仮データ（後でDBに差し替える） --}}
            @foreach(range(1, 10) as $i)
                <tr>
                    <td>11/{{ sprintf('%02d', $i) }}(木)</td>
                    <td>09:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <td>
                        <a href="{{ route('attendance.detail', ['id' => $i]) }}">
                            詳細
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</main>
@endsection

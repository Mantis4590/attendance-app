@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list_request.css') }}">
@endsection
 
@section('content')
<main class="list-request">
     <div class="list-request__title-text">申請一覧</div>

    {{-- タブ --}}
    <div class="list-request__tabs">
        <div class="list-request__tab list-request__tab--active">承認待ち</div>
        <div class="list-request__tab">承認済み</div>
    </div>

    {{-- テーブル --}}
    <div class="list-request__table-wrapper">
        <table class="list-request__table">
            <thead class="t__head">
                <tr class="t__head-tr">
                    <th class="t__head-th">状態</th>
                    <th class="t__head-th">名前</th>
                    <th class="t__head-th">対象日時</th>
                    <th class="t__head-th">申請理由</th>
                    <th class="t__head-th">申請日時</th>
                    <th class="t__head-th">詳細</th>
                </tr>
            </thead>
            <tbody class="t__body">
                {{-- 仮データ --}}
                <tr>
                    <td class="t__body-td">承認待ち</td>
                    <td class="t__body-td">西 怜奈</td>
                    <td class="t__body-td">2023/06/01</td>
                    <td class="t__body-td">遅延のため</td>
                    <td class="t__body-td">2023/06/02</td>
                    <td><a href="#" class="list-request__detail-link">詳細</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</main>
@endsection
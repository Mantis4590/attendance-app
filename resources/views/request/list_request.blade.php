@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/list_request.css') }}">
@endsection
 
@section('content')
<main class="list-request">
     <div class="list-request__title-text">申請一覧</div>

    {{-- タブ --}}
    <div class="list-request__tabs">
        <a href="{{ route('request.list', ['tab' => 'pending']) }}" class="list-request__tab {{ $tab === 'pending' ? 'list-request__tab--active' : '' }}">承認待ち</a>

        <a href="{{ route('request.list', ['tab' => 'approved']) }}" class="list-request__tab {{ $tab === 'approved' ? 'list-request__tab--active' : '' }}">承認済み</a>
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
                
                @forelse ($requests as $req)
                    <tr>
                        <td class="t__body-td">{{ $req->status === 'pending' ? '承認待ち' : '承認済み' }}</td>
                        <td class="t__body-td">{{ auth()->user()->name }}</td>
                        <td class="t__body-td">{{ optional($req->attendance)->date?->format('Y/m/d') ?? '-' }}</td>
                        <td class="t__body-td">{{ $req->reason }}</td>
                        <td class="t__body-td">{{ $req->updated_at->format('Y/m/d') }}</td>
                        <td class="t__body-td">
                            <a href="{{ route('attendance.detail', ['id' => $req->attendance_id]) }}" class="list-request__detail-link">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="t__body-td" colspan="6" style="text-align:center:">データがありません
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>
</main>
@endsection
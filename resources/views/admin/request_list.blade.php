@extends('layouts.admin_app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-request-list.css') }}">
@endsection

@section('content')
<main class="request-list">
    <div class="request-list__title">申請一覧</div>

    {{-- タブ --}}
    <div class="request-list__tabs">
        <a href="{{ route('admin.stamp_correction_request.list', ['tab' => 'pending']) }}" class="request-list__tab {{ $tab === 'pending' ? 'request-list__tab--active' : '' }}">承認待ち</a>

        <a href="{{ route('admin.stamp_correction_request.list', ['tab' => 'approved']) }}" class="request-list__tab {{ $tab === 'approved' ? 'request-list__tab--active' : '' }}">承認済み</a>
    </div>

    <div class="request-list__underline"></div>

    <table class="request-list__table">
        <thead class="t__head">
            <tr class="t__head-tr">
                <th class="request-list__th">状態</th>
                <th class="request-list__th">名前</th>
                <th class="request-list__th">対象日時</th>
                <th class="request-list__th">申請理由</th>
                <th class="request-list__th">申請日時</th>
                <th class="request-list__th">詳細</th>
            </tr>
        </thead>

        <tbody class="t__body">
            @forelse($requests as $req)
                <tr class="t__body-tr">
                    <td class="request-list__td">{{ $req->status === 'pending' ? '承認待ち' : '承認済み' }} </td>
                    <td class="request-list__td">{{ $req->user->name ?? '-' }}</td>
                    <td class="request-list__td">{{ optional(optional($req->attendance)->date)->format('Y/m/d') ?? '-' }}
                    </td>
                    <td class="request-list__td">{{ $req->note ?? '-' }}</td>
                    <td class="request-list__td">{{ optional($req->created_at)->format('Y/m/d') ?? '-' }}</td>
                    <td class="request-list__td request-list__td--link">
                        <a href="{{ route('admin.stamp_correction_request.show', $req) }}" class="request-list__link">詳細</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="request-list__empty" colspan="6"></td>
                </tr>
            @endforelse
        </tbody>
    </table>
</main>
@endsection
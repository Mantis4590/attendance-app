@extends('layouts.admin_app')

@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/staff.css') }}">
@endsection

@section('content')
<main class="staff">
    <div class="staff-title">スタッフ一覧</div>

     <table class="staff-table">
        <thead class="t__head">
            <tr class="t__head-tr">
                <th class="staff-th">名前</th>
                <th class="staff-th">メールアドレス</th>
                <th class="staff-th">月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $user)
                <tr>
                    <td class="staff-td">{{ $user->name }}</td>
                    <td class="staff-td">{{ $user->email }}</td>
                    <td class="staff-td">
                        <a href="{{ route('admin.attendance.staff', ['id' => $user->id]) }}" class="detail-btn">詳細</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
     </table>
</main>
@endsection
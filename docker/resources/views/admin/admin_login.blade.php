@extends('layouts.admin_app')
@section('title', '管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-login.css') }}">
@endsection

@section('content')
<div class="admin-login">
    <div class="admin-login__title">管理者ログイン</div>

    <form action="{{ route('admin.login.store') }}" method="POST" class="admin-login__form">
        @csrf
        <label for="email">メールアドレス</label>
        <input type="text" name="email" value="{{ old('email') }}">
        @error('email')
            <p class="admin-login__error">{{ $message }}</p>
        @enderror

        <label for="password">パスワード</label>
        <input type="password" name="password">
        @error('password')
            <p class="admin-login__error">{{ $message }}</p>
        @enderror


        <button type="submit">管理者ログインする</button>
    </form>
</div>
@endsection

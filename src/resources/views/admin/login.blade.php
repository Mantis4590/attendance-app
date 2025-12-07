@extends('layouts.app')

@section('title', '管理者ログイン')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin-login.css') }}">
@endsection

@section('content')
<main class="admin-login">
    <div class="admin-login__title">管理者ログイン</div>

    <form action="{{ route('admin.login.store') }}" method="POST" class="admin-login__form">
            @csrf

            <label for="email" class="admin-login__label">メールアドレス</label>
            <input type="text" name="email" class="admin-login__input">

            <label for="password" class="admin-login__label">パスワード</label>
            <input type="password" name="password" class="admin-login__input">

            <button type="submit" class="admin-login__btn">管理者ログインする</button>
    </form>
</main>
@endsection
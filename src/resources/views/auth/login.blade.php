@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<main class="login">
    <div class="login__title">
        <div class="login__title-content">ログイン</div>
    </div>

    <form action="{{ route('login.store') }}" method="POST" class="login__form">
        @csrf

        {{-- メールアドレス --}}
        <div class="login__group">
            <label for="email" class="login__label">メールアドレス</label>
            <input type="email" id="email" name="email" class="login__input" value="{{ old('email') }}">
            @error('email')
                <p class="login__error">{{ $message }}</p>
            @enderror
        </div>

        {{-- パスワード --}}
        <div class="login__group">
            <label for="password" class="login__label">パスワード</label>
            <input type="password" id="password" name="password" class="login__input">
            @error('password')
                <p class="login__error">{{ $message }}</p>
            @enderror
        </div>

        {{-- 登録ボタン --}}
        <button type="submit" class="login__button">ログインする</button>

        {{-- ログイン動線 --}}
        <div class="login__register-link">
            <a href="{{ route('register') }}" class="login__link-text">会員登録はこちら</a>
        </div>
    </form>
</main>
@endsection
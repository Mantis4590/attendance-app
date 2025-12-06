@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<main class="register">
    <div class="register__title">
        <div class="register__title-content">会員登録</div>
    </div>

    <form action="{{ route('register.store') }}" method="POST" class="register__form" novalidate>
        @csrf

        {{-- 名前 --}}
        <div class="register__group">
            <label for="name" class="register__label">名前</label>
            <input type="text" id="name" name="name" class="register__input" value="{{ old('name') }}">
            @error('name')
                <p class="register__error">{{ $message }}</p>
            @enderror
        </div>

        {{-- メールアドレス --}}
        <div class="register__group">
            <label for="email" class="register__label">メールアドレス</label>
            <input type="email" id="email" name="email" class="register__input" value="{{ old('email') }}">
            @error('email')
                <p class="register__error">{{ $message }}</p>
            @enderror
        </div>

        {{-- パスワード --}}
        <div class="register__group">
            <label for="password" class="register__label">パスワード</label>
            <input type="password" id="password" name="password" class="register__input">
            @error('password')
                <p class="register__error">{{ $message }}</p>
            @enderror
        </div>

        {{-- パスワード確認 --}}
        <div class="register__group">
            <label for="password_confirmation" class="register__label">パスワード確認</label>
            <input type="password" id="password_confirmation" name="password_confirmation" 
            class="register__input">
            @error('password_confirmation')
                <p class="register__error">{{ $message }}</p>
            @enderror
        </div>

        {{-- 登録ボタン --}}
        <button type="submit" class="register__button">登録する</button>

        {{-- ログイン動線 --}}
        <div class="register__login-link">
            <a href="{{ route('login') }}" class="register__link-text">ログインはこちら</a>
        </div>
    </form>
</main>
@endsection
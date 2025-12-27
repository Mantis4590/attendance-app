@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify_email.css') }}">
@endsection

@section('content')
<main class="verify-email">
    <p class="verify-email__message">
        登録していただいたメールアドレスに認証メールを送付しました。<br>メール認証を完了してください。
    </p>

    @if (session('message'))
        <p class="success">{{ session('message') }}</p>
    @endif
    <div class="verify-email__btn
    ">
        <a href="http://localhost:8025" target="_blank" class="verify-email__send">認証はこちらから</a>
    </div>
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <div class="resend-btn">
            <button type="submit" class="verify-email__resend">認証メールを再送する</button>
        </div>
    </form>
</main>
@endsection
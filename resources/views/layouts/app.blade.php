<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('css')
    <title>勤怠管理アプリ</title>
</head>
<body>
<header class="header">

    {{-- ロゴ --}}
    <div class="header__left">
        @auth
            @if(auth()->user()->hasVerifiedEmail())
                {{-- 認証済みユーザ- --}}
                <a href="{{ route('attendance.index') }}">
                    <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" class="header__logo">
                </a>
            @else
                {{-- 未認証ユーザー --}}
                <a href="{{ route('verification.notice') }}">
                    <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" class="header__logo">
                </a>
            @endif
        @endauth

        @guest
            <a href="{{ route('login') }}">
                <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" class="header__logo">
            </a>
        @endguest
    </div>

    {{-- ナビ --}}
    @auth
        @if(auth()->user()->hasVerifiedEmail())
            <nav class="header__nav">
                <a href="{{ route('attendance.index') }}" class="header__nav-item">勤怠</a>
                <a href="{{ route('attendance.list') }}" class="header__nav-item">勤怠一覧</a>
                <a href="{{ route('request.list') }}" class="header__nav-item">申請</a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <a href="#" class="header__nav-item"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        ログアウト
                    </a>
                </form>
            </nav>
        @endif
    @endauth

    {{-- ゲストのときはナビを表示しない --}}
    @guest
        {{-- 空 --}}
    @endguest

</header>

<main class="main @auth main--auth @endauth @guest main--guest @endguest">
    @yield('content')
</main>
</body>
</html>

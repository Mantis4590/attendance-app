<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '管理者画面')</title>

    {{-- CSS --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @yield('css')
</head>
<body>

<header class="admin-header">
    <div class="admin-header__left">
        <a href="#">
            <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" class="header__logo">
        </a>
    </div>

    {{-- ここをヘッダー右側に分離 --}}
    @auth('admin')
        <nav class="admin-header__nav">
            <a href="{{ route('admin.attendance.list') }}" class="header__nav-item">勤怠一覧</a>
            <a href="{{ route('admin.staff') }}" class="header__nav-item">スタッフ一覧</a>
            <a href="{{ route('admin.stamp_correction_request.list') }}" class="header__nav-item">申請一覧</a>

            <form action="{{ route('admin.logout') }}" method="POST">
                @csrf
                <button class="header__logout-btn">ログアウト</button>
            </form>
        </nav>
    @endauth
</header>

<main class="main {{ auth('admin')->check() ? 'main--auth' : 'main--guest' }}">
    @yield('content')
</main>

</body>
</html>

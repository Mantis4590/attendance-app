<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', '管理者画面')</title>

    {{-- 管理者専用 CSS --}}
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    @yield('css')
</head>
<body>

<header class="admin-header">
    <a href="{{ route('admin.login') }}">
        <img src="{{ asset('images/COACHTECHヘッダーロゴ.png') }}" class="header__logo">
    </a>

    @auth('admin')
    <nav class="admin-header__nav">
        <a href="{{ route('admin.attendance.list') }}">勤怠一覧</a>
        <a href="#">スタッフ一覧</a>
        <a href="#">申請一覧</a>

        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
            <button>ログアウト</button>
        </form>
    </nav>
    @endauth
</header>



<main class="admin-main">
    @yield('content')
</main>

</body>
</html>

<!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title')</title>

<link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('css')
</head>
<body class="app-body">

<header class="app-header">
<div class="app-header__inner">
    <div class="app-header__left">
    <img src="{{ asset('images/auth-header.png') }}" alt="COACHTECH" class="app-logo">
    </div>

    <nav class="app-nav">
    <a class="app-nav__link" href="{{ route('attendance.index') }}">勤怠</a>
    <a class="app-nav__link" href="{{ route('attendance.list') }}">勤怠一覧</a>
    <a class="app-nav__link" href="{{ route('stamp_correction_request.list') }}">申請</a>

    {{-- Fortifyログアウト（POST） --}}
    <form method="POST" action="{{ route('logout') }}" class="app-logout">
        @csrf
        <button type="submit" class="app-nav__link app-logout__btn">ログアウト</button>
    </form>
    </nav>
</div>
</header>

<main class="app-main">
    @yield('content')
</main>

</body>
</html>
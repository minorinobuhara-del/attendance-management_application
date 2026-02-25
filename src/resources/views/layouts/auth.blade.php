<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <!--@vite(['resources/css/app.css', 'resources/js/app.js'])-->
</head>
<body class="auth-body">
<header class="auth-header">
    <div class="auth-header__inner">
    <img src="{{ asset('images/auth-header.png') }}" alt="COACHTECH" class="auth-header__logo">
    </div>
</header>

<main class="auth-main">
    <div class="auth-card">
    @yield('content')
    </div>
</main>
</body>
</html>
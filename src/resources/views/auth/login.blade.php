@extends('layouts.auth')
@section('title', 'ログイン')
@section('content')
<h1 class="auth-title">ログイン</h1>

    @if ($errors->any())
    <div class="auth-errors">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    </div>
@endif

<form class="auth-form" method="POST" action="{{ route('login') }}">
    @csrf

    <div class="auth-field">
    <label class="auth-label" for="email">メールアドレス</label>
    <input class="auth-input" id="email" name="email" type="email" value="{{ old('email') }}">
    </div>

    <div class="auth-field">
    <label class="auth-label" for="password">パスワード</label>
    <input class="auth-input" id="password" name="password" type="password">
    </div>

    <button class="auth-btn" type="submit">ログインする</button>

    <div style="text-align:center;">
    <a class="auth-link" href="{{ route('register') }}">会員登録はこちら</a>
    </div>
</form>
@endsection
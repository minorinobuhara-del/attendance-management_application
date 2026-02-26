@extends('layouts.auth')
@section('title', '会員登録(一般ユーザー)')
@section('content')
<h1 class="auth-title">会員登録</h1>
    @if ($errors->any())
    <div class="auth-errors">
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    </div>
    @endif

<form class="auth-form" method="POST" action="{{ route('register') }}">
    @csrf

    <div class="auth-field">
    <label class="auth-label" for="name">名前</label>
    <input class="auth-input" id="name" name="name" type="text" value="{{ old('name') }}">
    </div>

    <div class="auth-field">
    <label class="auth-label" for="email">メールアドレス</label>
    <input class="auth-input" id="email" name="email" type="email" value="{{ old('email') }}">
    </div>

    <div class="auth-field">
    <label class="auth-label" for="password">パスワード</label>
    <input class="auth-input" id="password" name="password" type="password">
    </div>

    <div class="auth-field">
    <label class="auth-label" for="password_confirmation">パスワード確認</label>
    <input class="auth-input" id="password_confirmation" name="password_confirmation" type="password">
    </div>

    <button class="auth-btn" type="submit">登録する</button>

    <div class="auth-link-wrap">
    <a class="auth-link" href="{{ route('login') }}">ログインはこちら</a>
    </div>
</form>
@endsection
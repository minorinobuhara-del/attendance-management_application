@extends('layouts.auth')
@section('title', '管理者ログイン')

@section('content')
<h1 class="auth-title">管理者ログイン</h1>

@if ($errors->any())
<div class="auth-errors">
    <ul>
    @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach
    </ul>
</div>
@endif

<form class="auth-form" method="POST" action="{{ route('admin.login.store') }}">
    @csrf

    <div class="auth-field">
        <label class="auth-label" for="email">メールアドレス</label>
        <input class="auth-input" id="email" name="email" type="email" value="{{ old('email') }}">
    </div>

    <div class="auth-field">
        <label class="auth-label" for="password">パスワード</label>
        <input class="auth-input" id="password" name="password" type="password">
    </div>

    <button class="auth-btn" type="submit">管理者ログインする</button>
</form>
@endsection
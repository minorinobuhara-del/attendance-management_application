@extends('layouts.auth')
@section('title', 'メール認証誘導')

@section('content')
<p class="auth-note">
    登録していただいたメールアドレスに認証メールを送付しました。<br>
    メール認証を完了してください。
</p>

<div class="verify-btn-wrap">
    <a class="verify-btn" href="/attendance" onclick="document.getElementById('verify-form').submit();">
        認証はこちらから
    </a>
</div>

<form id="verify-form" method="POST" action="{{ route('verification.send') }}" style="margin-top:14px;">
    @csrf
    <button type="submit" class="auth-link" style="background:none;border:none;cursor:pointer;">
    認証メールを再送する
    </button>
</form>
@endsection
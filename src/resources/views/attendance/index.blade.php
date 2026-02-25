@extends('layouts.app')
@section('title', '勤怠登録画面')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance">
    <div class="attendance__pill">{{ $statusLabel }}</div>

    <div class="attendance__date">{{ $dateText }}</div>
    <div class="attendance__time">{{ $timeText }}</div>

    <div class="attendance__actions">
    <button class="btn btn-black" type="button">出勤</button>
    </div>
</div>
@endsection
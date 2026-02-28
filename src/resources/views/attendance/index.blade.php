@extends('layouts.app')
@section('title', '出勤登録画面(一般ユーザー)')

@section('content')
<div class="attendance-page">
<div class="attendance-card">

    <div class="attendance-status">{{ $status }}</div>

    <div class="attendance-date" id="js-date"></div>
    <div class="attendance-time" id="js-time"></div>

    @if (session('message'))
    <div class="attendance-message">{{ session('message') }}</div>
    @endif

    <div class="attendance-actions">
    {{-- 勤務外：出勤ボタン --}}
    @if ($status === '勤務外')
        <form method="POST" action="{{ route('attendance.clockIn') }}">
        @csrf
        <button class="btn btn-black" type="submit">出勤</button>
        </form>
    @endif

    {{-- 出勤中：退勤 + 休憩入 --}}
    @if ($status === '出勤中')
        <form method="POST" action="{{ route('attendance.clockOut') }}">
        @csrf
        <button class="btn btn-black" type="submit">退勤</button>
        </form>

        <form method="POST" action="{{ route('attendance.breakIn') }}">
        @csrf
        <button class="btn btn-white" type="submit">休憩入</button>
        </form>
    @endif

    {{-- 休憩中：休憩戻 --}}
    @if ($status === '休憩中')
        <form method="POST" action="{{ route('attendance.breakOut') }}">
        @csrf
        <button class="btn btn-white" type="submit">休憩戻</button>
        </form>
    @endif
    </div>

    {{-- 退勤済：メッセージだけ --}}
    @if (session('message') && $status !== '退勤済')
    <div class="attendance-finished">{{ session('message') }}</div>
    @endif

  </div>
</div>

<script>
(() => {
const wdays = ['日','月','火','水','木','金','土'];
const pad = n => String(n).padStart(2,'0');

const render = () => {
    const now = new Date();
    const y = now.getFullYear();
    const m = now.getMonth() + 1;
    const d = now.getDate();
    const wd = wdays[now.getDay()];
    const hh = pad(now.getHours());
    const mm = pad(now.getMinutes());

    document.getElementById('js-date').textContent = `${y}年${m}月${d}日(${wd})`;
    document.getElementById('js-time').textContent = `${hh}:${mm}`;
};

render();
setInterval(render, 1000);
})();
</script>
@endsection
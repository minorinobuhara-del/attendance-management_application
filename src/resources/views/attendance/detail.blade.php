@extends('layouts.app')
@section('title', '勤怠詳細(一般ユーザー)')

@section('content')
<div class="page">
<div class="page-head">
    <span class="page-bar"></span>
    <h1 class="page-title">勤怠詳細</h1>
</div>

<div class="detail-card">
    <table class="detail-table">
    <tr>
        <th>名前</th>
        <td>{{ auth()->user()->name }}</td>
    </tr>
    <tr>
        <th>日付</th>
        <td>{{ \Carbon\Carbon::parse($attendance->work_date)->translatedFormat('Y年 n月j日') }}</td>
    </tr>
    <tr>
        <th>出勤・退勤</th>
        <td>
        @if($pendingRequest)
            {{ $attendance->clock_in }} ～ {{ $attendance->clock_out }}
        @else
        <form method="POST" action="{{ route('attendance.detail.request', ['id' => $attendance->id]) }}">
        @csrf
        <div class="time-row">
                <input class="time-input" name="clock_in" value="{{ old('clock_in', $attendance->clock_in) }}">
                <span class="time-tilde">～</span>
                <input class="time-input" name="clock_out" value="{{ old('clock_out', $attendance->clock_out) }}">
        </div>
        @endif
        </td>
    </tr>

    @php
        $breaks = $attendance->breaks;
    @endphp

    {{-- 休憩：既存回数分 + 追加1行 --}}
    @for($i=0; $i < $breaks->count() + 1; $i++)
        @php
        $b = $breaks[$i] ?? null;
        $label = $i === 0 ? '休憩' : "休憩".($i+1);
        @endphp
        <tr>
        <th>{{ $label }}</th>
        <td>
            @if($pendingRequest)
            {{ $b?->start ?? '' }} ～ {{ $b?->end ?? '' }}
            @else
            <div class="time-row">
                <input class="time-input" name="breaks[{{ $i }}][start]" value="{{ old("breaks.$i.start", $b?->start) }}">
                <span class="time-tilde">～</span>
                <input class="time-input" name="breaks[{{ $i }}][end]" value="{{ old("breaks.$i.end", $b?->end) }}">
            </div>
            @endif
        </td>
        </tr>
    @endfor

    <tr>
        <th>備考</th>
        <td>
        @if($pendingRequest)
            {{ $attendance->note ?? '' }}
        @else
            <textarea class="note-input" name="note">{{ old('note', $attendance->note) }}</textarea>
        @endif
        </td>
    </tr>
    </table>
</div>

    @if ($errors->any())
    <div class="error-box">
    <ul>
        @foreach($errors->all() as $e)
        <li>{{ $e }}</li>
        @endforeach
    </ul>
    </div>
    @endif

    @if($pendingRequest)
    <p class="warn-text">※承認待ちのため修正はできません。</p>
    @else
    <div class="detail-actions">
    <button class="black-mini" type="submit">修正</button>
    </div>
    </form>
@endif
</div>
@endsection
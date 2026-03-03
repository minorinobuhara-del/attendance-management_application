@extends('layouts.admin')
@section('title', '申請詳細（管理者）')

@section('content')
<div class="page page--pad">

<div class="page-head">
    <span class="page-bar"></span>
    <h1 class="page-title">勤怠詳細</h1>
</div>

@if(session('message'))
    <p class="attendance-message">{{ session('message') }}</p>
@endif

@php
    $date = optional($attendance)->work_date;
    $clockIn  = $payload['clock_in'] ?? '';
    $clockOut = $payload['clock_out'] ?? '';
    $note = $payload['note'] ?? '';
    $breaks = $payload['breaks'] ?? [];
@endphp

<div class="detail-card">
    <table class="detail-table">
    <tr>
        <th>名前</th>
        <td class="detail-strong">{{ $req->user->name ?? '' }}</td>
    </tr>
    <tr>
        <th>日付</th>
        <td class="detail-strong">
        {{ $date ? \Carbon\Carbon::parse($date)->translatedFormat('Y年n月j日') : '' }}
        </td>
    </tr>
    <tr>
        <th>出勤・退勤</th>
        <td>
        <span class="detail-strong">{{ $clockIn }}</span>
        <span class="time-tilde">～</span>
        <span class="detail-strong">{{ $clockOut }}</span>
        </td>
    </tr>

    @for($i=0; $i < max(2, count($breaks)); $i++)
        @php
        $b = $breaks[$i] ?? ['start'=>'','end'=>''];
        $label = $i === 0 ? '休憩' : "休憩".($i+1);
        @endphp
        <tr>
        <th>{{ $label }}</th>
        <td>
            <span class="detail-strong">{{ $b['start'] ?? '' }}</span>
            <span class="time-tilde">～</span>
            <span class="detail-strong">{{ $b['end'] ?? '' }}</span>
        </td>
        </tr>
    @endfor

    <tr>
        <th>備考</th>
        <td class="detail-strong">{{ $note }}</td>
    </tr>
    </table>
</div>

<div class="approve-actions">
    @if($req->status === 'approved')
    <span class="badge-approved">承認済み</span>
    @else
    <form method="POST" action="{{ route('admin.stamp_correction_request.approve', $req) }}">
        @csrf
        @method('PUT')
        <button type="submit" class="black-mini">承認</button>
    </form>
    @endif
</div>

</div>
@endsection
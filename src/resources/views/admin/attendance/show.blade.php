@extends('layouts.admin')
@section('title', '勤怠詳細（管理者）')

@section('content')
<div class="admin-page">
    <div class="page-head admin-head">
        <span class="page-bar"></span>
        <h1 class="page-title">勤怠詳細</h1>
    </div>

    <div class="detail-card">
        <table class="detail-table">
            <tr>
                <th>名前</th>
                <td class="detail-strong">{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td class="detail-strong">
                    {{ \Carbon\Carbon::parse($attendance->work_date)->translatedFormat('Y年n月j日') }}
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <span class="detail-strong">
                        {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
                    </span>
                    <span class="time-tilde">～</span>
                    <span class="detail-strong">
                        {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
                    </span>
                </td>
            </tr>

            @php $breaks = $attendance->breaks; @endphp
            @for($i=0; $i < $breaks->count(); $i++)
                @php
                    $b = $breaks[$i];
                    $label = $i === 0 ? '休憩' : '休憩'.($i+1);
                    $start = $b->break_start ? \Carbon\Carbon::parse($b->break_start)->format('H:i') : '';
                    $end   = $b->break_end ? \Carbon\Carbon::parse($b->break_end)->format('H:i') : '';
                @endphp
                <tr>
                    <th>{{ $label }}</th>
                    <td>
                        <span class="detail-strong">{{ $start }}</span>
                        <span class="time-tilde">～</span>
                        <span class="detail-strong">{{ $end }}</span>
                    </td>
                </tr>
            @endfor
        </table>
    </div>
</div>
@endsection
@extends('layouts.admin')
@section('title', '勤怠一覧（管理者）')

@section('content')
<div class="admin-page">
    <div class="page-head admin-head">
        <span class="page-bar"></span>
        <h1 class="page-title">{{ \Carbon\Carbon::parse($date)->format('Y年n月j日') }}の勤怠</h1>
    </div>

    <div class="admin-datebar">
        <a class="admin-datebar__btn" href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}">← 前日</a>

        <div class="admin-datebar__center">
            <span class="admin-datebar__icon">📅</span>
            <span class="admin-datebar__label">{{ $label }}</span>
        </div>

        <a class="admin-datebar__btn" href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}">翌日 →</a>
    </div>

    <div class="table-card">
        <table class="table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                    <tr>
                        <td>{{ $r['user_name'] }}</td>
                        <td>{{ $r['clock_in'] }}</td>
                        <td>{{ $r['clock_out'] }}</td>
                        <td>{{ $r['break'] }}</td>
                        <td>{{ $r['total'] }}</td>
                        <td>
                            @if($r['attendance_id'])
                                <a class="detail-link" href="{{ route('admin.attendance.show', $r['attendance_id']) }}">詳細</a>
                            @else
                                {{-- 勤怠が無い日は空欄 --}}
                                <span class="muted"> </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
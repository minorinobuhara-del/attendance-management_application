@extends('layouts.admin')
@section('title', 'スタッフ別勤怠一覧（管理者）')

@section('content')
<div class="admin-page">
    <div class="admin-container">
    <div class="page-head">
        <span class="page-bar"></span>
        <h1 class="page-title">{{ $user->name }}さんの勤怠</h1>
    </div>

    {{-- 月切替 --}}
    <div class="month-card">
        <a class="month-card__btn" href="{{ route('admin.attendance.staff', ['user' => $user->id, 'month' => $prevMonth]) }}">← 前月</a>

        <div class="month-card__center">
            <span class="month-card__icon">📅</span>
            <span class="month-card__label">{{ $monthLabel }}</span>
        </div>

        <a class="month-card__btn" href="{{ route('admin.attendance.staff', ['user' => $user->id, 'month' => $nextMonth]) }}">翌月 →</a>
    </div>

    {{-- 一覧 --}}
    <div class="table-card table-card--wide">
        <table class="table">
            <thead>
                <tr>
                    <th>日付</th>
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
                        <td>{{ $r['date_label'] }}</td>
                        <td>{{ $r['clock_in'] }}</td>
                        <td>{{ $r['clock_out'] }}</td>
                        <td>{{ $r['break'] }}</td>
                        <td>{{ $r['total'] }}</td>
                        <td>
                            @if($r['attendance_id'])
                                <a class="detail-link" href="{{ route('admin.attendance.show', $r['attendance_id']) }}">詳細</a>
                            @else
                                <span class="detail-link is-disabled">詳細</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- CSV --}}
    <div class="csv-actions">
        <a class="black-mini" href="{{ route('admin.attendance.staff.csv', ['user' => $user->id, 'month' => $base->format('Y-m')]) }}">
            CSV出力
        </a>
    </div>
</div>
</div>
@endsection
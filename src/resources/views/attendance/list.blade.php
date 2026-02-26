@extends('layouts.app')
@section('title', '勤怠一覧(一般ユーザー)')

@section('content')
<div class="list-page">
    <div class="list-card">

        <div class="list-title">
            <span class="list-title__bar">|</span>
            <span>勤怠一覧</span>
        </div>

        <div class="list-month">
            <a class="list-month__btn" href="{{ route('attendance.list', ['month' => $prevMonth]) }}">← 前月</a>

            <div class="list-month__center">
                <span class="list-month__icon">📅</span>
                <span class="list-month__label">{{ $currentMonthLabel }}</span>
            </div>

            <a class="list-month__btn" href="{{ route('attendance.list', ['month' => $nextMonth]) }}">翌月 →</a>
        </div>

        <div class="list-table-wrap">
            <table class="list-table">
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
                    @foreach ($rows as $r)
                        <tr>
                            <td>{{ $r['label'] }}</td>
                            <td>{{ $r['clock_in'] }}</td>
                            <td>{{ $r['clock_out'] }}</td>
                            <td>{{ $r['break'] }}</td>
                            <td>{{ $r['total'] }}</td>
                            <td>
                                <a class="list-detail" href="{{ route('attendance.show', ['date' => $r['date']]) }}">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
@extends('layouts.app')
@section('title', '申請一覧')

@section('content')
<div class="page">
<div class="page-head">
    <span class="page-bar"></span>
    <h1 class="page-title">申請一覧</h1>
</div>

<div class="tabs">
    <button class="tab is-active" data-tab="pending">承認待ち</button>
    <button class="tab" data-tab="approved">承認済み</button>
</div>

<div class="table-card" id="tab-pending">
    <table class="table">
    <thead>
        <tr>
        <th>状態</th><th>名前</th><th>対象日時</th><th>申請理由</th><th>申請日時</th><th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pending as $r)
        <tr>
        <td>承認待ち</td>
        <td>{{ $r->user->name }}</td>
        <td>{{ $r->attendance->work_date }}</td>
        <td>{{ $r->payload['note'] ?? '' }}</td>
        <td>{{ $r->created_at->format('Y/m/d') }}</td>
        <td><a class="detail-link" href="{{ route('requests.show', $r) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
    </table>
</div>

<div class="table-card is-hidden" id="tab-approved">
    <table class="table">
    <thead>
        <tr>
        <th>状態</th><th>名前</th><th>対象日時</th><th>申請理由</th><th>申請日時</th><th>詳細</th>
        </tr>
    </thead>
    <tbody>
        @foreach($approved as $r)
        <tr>
        <td>承認済み</td>
        <td>{{ $r->user->name }}</td>
        <td>{{ $r->attendance->work_date }}</td>
        <td>{{ $r->payload['note'] ?? '' }}</td>
        <td>{{ $r->created_at->format('Y/m/d') }}</td>
        <td><a class="detail-link" href="{{ route('requests.show', $r) }}">詳細</a></td>
        </tr>
        @endforeach
    </tbody>
    </table>
</div>
</div>

<script>
document.querySelectorAll('.tab').forEach(btn => {
btn.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach(b => b.classList.remove('is-active'));
    btn.classList.add('is-active');

    document.getElementById('tab-pending').classList.toggle('is-hidden', btn.dataset.tab !== 'pending');
    document.getElementById('tab-approved').classList.toggle('is-hidden', btn.dataset.tab !== 'approved');
});
});
</script>
@endsection
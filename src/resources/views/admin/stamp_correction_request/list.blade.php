@extends('layouts.admin')
@section('title', '申請一覧（管理者）')

@section('content')
<div class="request-page">
  <div class="request-card">

    <div class="page-head admin-head">
      <span class="page-bar"></span>
      <h1 class="page-title">申請一覧</h1>
    </div>

    <div class="tabs request-tabs">
      <button type="button" class="tab is-active" data-tab="pending">承認待ち</button>
      <button type="button" class="tab" data-tab="approved">承認済み</button>
    </div>

    {{-- 承認待ち --}}
    <div class="table-card" id="tab-pending">
    <table class="table">
        <thead>
        <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
        </tr>
        </thead>
        <tbody>
        @forelse($pending as $r)
        @php
        $payload = is_array($r->payload)
        ? $r->payload
        : (json_decode($r->payload ?? '[]', true) ?: []);
        @endphp
        <tr>
            <td>承認待ち</td>
            <td>{{ $r->user->name ?? '' }}</td>
            <td>{{ optional($r->attendance)->work_date }}</td>
            <td>{{ $payload['note'] ?? '' }}</td>
            <td>{{ $r->created_at?->format('Y/m/d') }}</td>
            <td><a class="detail-link" href="{{ route('admin.stamp_correction_request.show', $r) }}">詳細</a></td>
        </tr>
        @empty
        <tr><td colspan="6" class="muted">承認待ちはありません</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>

    {{-- 承認済み --}}
    <div class="table-card is-hidden" id="tab-approved">
    <table class="table">
        <thead>
        <tr>
            <th>状態</th>
            <th>名前</th>
            <th>対象日時</th>
            <th>申請理由</th>
            <th>申請日時</th>
            <th>詳細</th>
        </tr>
        </thead>
        <tbody>
        @forelse($approved as $r)
        @php
        $payload = is_array($r->payload)
        ? $r->payload
        : (json_decode($r->payload ?? '[]', true) ?: []);
        @endphp
        <tr>
            <td>承認済み</td>
            <td>{{ $r->user->name ?? '' }}</td>
            <td>{{ optional($r->attendance)->work_date }}</td>
            <td>{{ $payload['note'] ?? '' }}</td>
            <td>{{ $r->created_at?->format('Y/m/d') }}</td>
            <td><a class="detail-link" href="{{ route('admin.stamp_correction_request.show', $r) }}">詳細</a></td>
        </tr>
        @empty
        <tr><td colspan="6" class="muted">承認済みはありません</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>

</div>
</div>

<script>
const tabs = document.querySelectorAll('[data-tab]');
const pending = document.getElementById('tab-pending');
const approved = document.getElementById('tab-approved');

tabs.forEach(btn => {
    btn.addEventListener('click', () => {
    tabs.forEach(b => b.classList.remove('is-active'));
    btn.classList.add('is-active');

    const key = btn.dataset.tab;
    pending.classList.toggle('is-hidden', key !== 'pending');
    approved.classList.toggle('is-hidden', key !== 'approved');
    });
});
</script>
@endsection
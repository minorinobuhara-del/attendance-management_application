@extends('layouts.admin')
@section('title', 'スタッフ一覧（管理者）')

@section('content')
<div class="admin-page">
    <div class="page-head">
        <span class="page-bar"></span>
        <h1 class="page-title">スタッフ一覧</h1>
    </div>

    <div class="table-card">
        <table class="table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td>
                            <a class="detail-link" href="{{ route('admin.attendance.month', $u) }}">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
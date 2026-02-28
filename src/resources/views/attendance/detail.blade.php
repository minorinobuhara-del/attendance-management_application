@extends('layouts.app')
@section('title', '勤怠詳細(一般ユーザー)')

@section('content')
<div class="page">

  <div class="page-head">
    <span class="page-bar"></span>
    <h1 class="page-title">勤怠詳細</h1>
  </div>

  <div class="detail-wrap">

    @if(!$pendingRequest)
      <form method="POST" action="{{ route('attendance.detail.request', ['id' => $attendance->id]) }}">
      @csrf
    @endif

    <div class="detail-card">
      <table class="detail-table">
        <tr>
          <th>名前</th>
          <td class="detail-strong">{{ auth()->user()->name }}</td>
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
            @if($pendingRequest)
              <span class="detail-strong">
                {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '' }}
              </span>
              <span class="time-tilde">～</span>
              <span class="detail-strong">
                {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '' }}
              </span>
            @else
              <div class="time-row">
                <input class="time-input" name="clock_in"
                  value="{{ old('clock_in', $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') : '') }}">
                <span class="time-tilde">～</span>
                <input class="time-input" name="clock_out"
                  value="{{ old('clock_out', $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') : '') }}">
              </div>
            @endif
          </td>
        </tr>

        @php $breaks = $attendance->breaks; @endphp
        @for($i=0; $i < $breaks->count() + 1; $i++)
          @php
            $b = $breaks[$i] ?? null;
            $label = $i === 0 ? '休憩' : "休憩".($i+1);
            $start = $b?->break_start ? \Carbon\Carbon::parse($b->break_start)->format('H:i') : '';
            $end   = $b?->break_end ? \Carbon\Carbon::parse($b->break_end)->format('H:i') : '';
          @endphp
          <tr>
            <th>{{ $label }}</th>
            <td>
              @if($pendingRequest)
                <span class="detail-strong">{{ $start }}</span>
                <span class="time-tilde">～</span>
                <span class="detail-strong">{{ $end }}</span>
              @else
                <div class="time-row">
                  <input class="time-input" name="breaks[{{ $i }}][start]" value="{{ old("breaks.$i.start", $start) }}">
                  <span class="time-tilde">～</span>
                  <input class="time-input" name="breaks[{{ $i }}][end]" value="{{ old("breaks.$i.end", $end) }}">
                </div>
              @endif
            </td>
          </tr>
        @endfor

        <tr>
          <th>備考</th>
          <td>
            @if($pendingRequest)
              <span class="detail-strong">{{ $attendance->note ?? '' }}</span>
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

  </div>{{-- /.detail-wrap --}}
</div>{{-- /.page --}}
@endsection
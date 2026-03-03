<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class AdminStampCorrectionRequestController extends Controller
{
    // /admin/stamp_correction_request/list?tab=pending|approved
    public function index()
    {
        $pending = AttendanceRequest::with(['user', 'attendance'])
            ->where('status', 'pending')
            ->latest()
            ->get();

        $approved = AttendanceRequest::with(['user', 'attendance'])
            ->where('status', 'approved')
            ->latest()
            ->get();

        return view('admin.stamp_correction_request.list', compact('pending', 'approved'));
    }

    // 詳細（承認画面）
    public function show(AttendanceRequest $attendanceRequest)
    {
        $attendanceRequest->load(['user', 'attendance.breaks']);


        $payload = is_array($attendanceRequest->payload)
        ? $attendanceRequest->payload
        : (json_decode($attendanceRequest->payload ?? '[]', true) ?: []);

        return view('admin.stamp_correction_request.show', [
            'req' => $attendanceRequest,
            'attendance' => $attendanceRequest->attendance,
            'payload' => $payload,
        ]);
    }

    // 承認（勤怠へ反映 + status更新）
    public function approve(AttendanceRequest $attendanceRequest)
    {
        if ($attendanceRequest->status !== 'pending') {
            return redirect()
                ->route('admin.stamp_correction_request.show', $attendanceRequest)
                ->with('message', 'この申請は既に承認済みです');
        }
        $attendanceRequest->load(['attendance.breaks']);
        $payload = is_array($attendanceRequest->payload)
        ? $attendanceRequest->payload
        : (json_decode($attendanceRequest->payload ?? '[]', true) ?: []);

        DB::transaction(function () use ($attendanceRequest, $payload) {

            $attendance = $attendanceRequest->attendance;
            $date = Carbon::parse($attendance->work_date)->toDateString();

            // 勤怠本体更新
            $attendance->update([
                'clock_in'  => !empty($payload['clock_in'])  ? Carbon::parse($date.' '.$payload['clock_in']) : null,
                'clock_out' => !empty($payload['clock_out']) ? Carbon::parse($date.' '.$payload['clock_out']) : null,
                'note'      => $payload['note'] ?? null,
            ]);

            // 休憩更新（シンプルに全削除→作り直し）
            if (isset($payload['breaks']) && is_array($payload['breaks'])) {
                $attendance->breaks()->delete();

                foreach ($payload['breaks'] as $b) {
                    $start = $b['start'] ?? null;
                    $end   = $b['end'] ?? null;

                    // 両方nullの行はスキップ
                    if (!$start && !$end) continue;

                    $attendance->breaks()->create([
                        'break_start' => $start ? Carbon::parse($date.' '.$start) : null,
                        'break_end'   => $end   ? Carbon::parse($date.' '.$end) : null,
                    ]);
                }

            }
            // 申請を承認済みに
            $attendanceRequest->update([
                'status' => 'approved',
            ]);
        });
        return redirect()
            ->route('admin.stamp_correction_request.show', $attendanceRequest)
            ->with('message', '承認しました');
    }


}

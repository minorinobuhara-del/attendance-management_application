<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use App\Http\Requests\Admin\AdminAttendanceUpdateRequest;
use Illuminate\Support\Facades\DB;

class AdminAttendanceController extends Controller
{
    // /admin/attendance/list?date=2023-06-01
    public function index(Request $request)
    {
        $date = $request->query('date')
            ? Carbon::parse($request->query('date'))->toDateString()
            : Carbon::today()->toDateString();

        $prevDate = Carbon::parse($date)->subDay()->toDateString();
        $nextDate = Carbon::parse($date)->addDay()->toDateString();
        $label = Carbon::parse($date)->format('Y/m/d');

        // その日の勤怠をまとめて取得
        $attendances = Attendance::with(['user', 'breaks'])
            ->where('work_date', $date)
            ->get()
            ->keyBy('user_id');

        // 「全ユーザー」を並べて、勤怠が無い人は空欄にする
        $users = User::orderBy('id')->get();

        $rows = [];
        foreach ($users as $user) {
            $a = $attendances->get($user->id);

            $clockIn = $a?->clock_in ? Carbon::parse($a->clock_in)->format('H:i') : '';
            $clockOut = $a?->clock_out ? Carbon::parse($a->clock_out)->format('H:i') : '';

            // 休憩合計（分）
            $breakMinutes = 0;
            if ($a) {
                foreach ($a->breaks as $b) {
                    if ($b->break_start && $b->break_end) {
                        $breakMinutes += Carbon::parse($b->break_start)
                            ->diffInMinutes(Carbon::parse($b->break_end));
                    }
                }
            }
            $breakText = $breakMinutes > 0
                ? sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60)
                : '';

            // 合計（出勤〜退勤 - 休憩）
            $totalText = '';
            if ($a && $a->clock_in && $a->clock_out) {
                $workMinutes = Carbon::parse($a->clock_in)->diffInMinutes(Carbon::parse($a->clock_out));
                $net = max(0, $workMinutes - $breakMinutes);
                $totalText = sprintf('%d:%02d', intdiv($net, 60), $net % 60);
            }

            $rows[] = [
                'user_name' => $user->name,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'break' => $breakText,
                'total' => $totalText,
                'attendance_id' => $a?->id, // 無い日は null
            ];
    }
        return view('admin.attendance.list', compact('date','prevDate','nextDate','label','rows'));
    }

    public function show(\App\Models\Attendance $attendance)
    {
    $attendance->load('breaks', 'user');

    $pendingRequest = \App\Models\AttendanceRequest::where('attendance_id', $attendance->id)
        ->where('status', 'pending')
        ->exists();

    return view('admin.attendance.show', compact('attendance', 'pendingRequest'));
    }

    public function update(AdminAttendanceUpdateRequest $request, Attendance $attendance)
    {
    DB::transaction(function () use ($request, $attendance) {

        $date = $attendance->work_date;

        $attendance->update([
            'clock_in'  => Carbon::parse($date.' '.$request->clock_in),
            'clock_out' => Carbon::parse($date.' '.$request->clock_out),
            'note'      => $request->note,
        ]);

        // break 更新処理もここに書く
    });

    return redirect()
        ->route('admin.attendance.show', $attendance)
        ->with('message', '修正しました');
    }
}

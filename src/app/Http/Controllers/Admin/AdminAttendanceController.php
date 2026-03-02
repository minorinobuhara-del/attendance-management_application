<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

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

    // /admin/attendance/{attendance}
    public function show(Attendance $attendance)
    {
        $attendance->load(['user', 'breaks']);

        // 詳細画面側で表示用に整形してもOK（bladeでformatしてもOK）
        return view('admin.attendance.show', compact('attendance'));
    }
}

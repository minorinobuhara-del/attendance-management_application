<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\BreakTime;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        $attendance = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->where('work_date', $today)
            ->first();

        $status = $attendance?->statusLabel() ?? '勤務外';

        return view('attendance.index', compact('attendance','status'));

    }

    public function clockIn(Request $request)
    {
        $user = $request->user();
        $today = now()->toDateString();

        DB::transaction(function () use ($user, $today) {
            $attendance = Attendance::firstOrCreate(
                ['user_id' => $user->id, 'work_date' => $today],
                ['clock_in' => now()]
            );

            // 既に出勤済みなら何もしない（1日1回）
            if ($attendance->clock_in) return;

            $attendance->update(['clock_in' => now()]);
        });

        return redirect()->route('attendance.index');
    }

    public function breakIn(Request $request)
    {
        $attendance = $this->todayAttendanceOrFail($request);

        // 出勤中のみ、休憩開始できる（退勤済/休憩中は不可）
        if (!$attendance->clock_in || $attendance->clock_out || $attendance->latestOpenBreak()) {
            return redirect()->route('attendance.index');
        }

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
        ]);

        return redirect()->route('attendance.index');
    }

    public function breakOut(Request $request)
    {
        $attendance = $this->todayAttendanceOrFail($request);

        $open = $attendance->latestOpenBreak();
        if (!$open || $attendance->clock_out) {
            return redirect()->route('attendance.index');
        }

        $open->update(['break_end' => now()]);

        return redirect()->route('attendance.index');
    }

    public function clockOut(Request $request)
    {
        $attendance = $this->todayAttendanceOrFail($request);

        // 出勤中のみ退勤OK（休憩中は先に休憩戻しても良いが、ここでは禁止）
        if (!$attendance->clock_in || $attendance->clock_out || $attendance->latestOpenBreak()) {
            return redirect()->route('attendance.index');
        }

        $attendance->update(['clock_out' => now()]);

        return redirect()->route('attendance.index');
    }

    private function todayAttendanceOrFail(Request $request): Attendance
    {
        $today = now()->toDateString();

        return Attendance::with('breaks')
            ->where('user_id', $request->user()->id)
            ->where('work_date', $today)
            ->firstOrFail();
    }
}

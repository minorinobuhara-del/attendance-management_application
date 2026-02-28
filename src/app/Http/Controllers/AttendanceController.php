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
        $userId = Auth::id();
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('work_date', $today)
            ->with('breaks')
            ->first();

            // ステータス判定
        $status = '勤務外';

        if ($attendance && $attendance->clock_in && !$attendance->clock_out) {
            $status = '出勤中';

            // 休憩中判定（最後の休憩が end なしなら休憩中）
            $lastBreak = $attendance->breaks->last();
            if ($lastBreak && $lastBreak->break_start && !$lastBreak->break_end) {
                $status = '休憩中';
            }
        }

        if ($attendance && $attendance->clock_out) {
            $status = '退勤済';
        }

        return view('attendance.index', compact('attendance','status'));
    }

    //出勤
    public function clockIn(Request $request)
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        Attendance::firstOrCreate(
            ['user_id' => $userId, 'work_date' => $today],
            ['clock_in' => now()]
        );

        return redirect()->route('attendance.index');
    }

    //休憩入
    public function breakIn(Request $request)
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('work_date', $today)
            ->firstOrFail();

        BreakTime::create([
            'attendance_id' => $attendance->id,
            'break_start' => now(),
            'break_end' => null,
        ]);

        return redirect()->route('attendance.index');
    }

    //休憩戻
    public function breakOut(Request $request)
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('work_date', $today)
            ->firstOrFail();

        $break = BreakTime::where('attendance_id', $attendance->id)
            ->whereNull('break_end')
            ->latest()
            ->firstOrFail();

        $break->update(['break_end' => now()]);

        return redirect()->route('attendance.index');
    }

    //退勤
    public function clockOut(Request $request)
    {
        $userId = Auth::id();
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $userId)
            ->where('work_date', $today)
            ->firstOrFail();

        // 退勤は1日1回
        if (!$attendance->clock_out) {
            $attendance->update(['clock_out' => now()]);
        }

        return redirect()->route('attendance.index')->with('message', 'お疲れ様でした。');
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

<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AttendanceListController extends Controller
{
    public function index(Request $request)
    {
        // 表示月（未指定なら今月）
        $month = $request->query('month'); // "2023-06" 形式
        $currentMonth = $month
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : Carbon::now()->startOfMonth();

        $start = $currentMonth->copy()->startOfMonth();
        $end   = $currentMonth->copy()->endOfMonth();

        // 前月・翌月
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // 月内の勤怠をまとめて取得（休憩も）
        $attendances = Attendance::query()
            ->where('user_id', Auth::id())
            ->whereDate('work_date', '>=', $start->toDateString())
            ->whereDate('work_date', '<=', $end->toDateString())
            ->with('breaks') // Attendance::breaks() がある前提
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->work_date)->toDateString());

            // その月の日付を全部作る（勤怠が無い日も行を作る）
        $days = CarbonPeriod::create($start, $end);

        $rows = [];
        foreach ($days as $day) {
            $dateKey = $day->toDateString();
            $attendance = $attendances->get($dateKey);

            $clockIn  = $attendance?->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('H:i') : '';
            $clockOut = $attendance?->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('H:i') : '';

            // 休憩合計（分）
            $breakMinutes = 0;
            if ($attendance) {
                foreach ($attendance->breaks as $b) {
                    if ($b->break_in_at && $b->break_out_at) {
                        $breakMinutes += Carbon::parse($b->break_in_at)
                            ->diffInMinutes(Carbon::parse($b->break_out_at));
                    }
                }
            }

            $breakText = $breakMinutes > 0
                ? sprintf('%d:%02d', intdiv($breakMinutes, 60), $breakMinutes % 60)
                : '';

            // 勤務合計（出勤〜退勤 - 休憩）
            $totalText = '';
            if ($attendance && $attendance->clock_in_at && $attendance->clock_out_at) {
                $workMinutes = Carbon::parse($attendance->clock_in_at)
                    ->diffInMinutes(Carbon::parse($attendance->clock_out_at));

                $net = max(0, $workMinutes - $breakMinutes);
                $totalText = sprintf('%d:%02d', intdiv($net, 60), $net % 60);
            }

            $rows[] = [
                'id' => $attendance?->id,
                'label' => $day->format('m/d') . '(' . ['日','月','火','水','木','金','土'][$day->dayOfWeek] . ')',
                'date' => $dateKey,
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'break' => $breakText,
                'total' => $totalText,
                'hasAttendance' => (bool)$attendance,
            ];
        }

        return view('attendance.list', [
            'currentMonthLabel' => $currentMonth->format('Y/m'),
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
            'rows' => $rows,
        ]);
    }

    // 詳細（ひとまずダミー。後で勤怠詳細を作る）
    public function show(string $date)
    {
        // 例：/attendance/2023-06-01
        // ここは「勤怠詳細画面」を作る段階で実装します
        return view('attendance.show', ['date' => $date]);
    }

    }

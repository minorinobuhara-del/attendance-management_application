<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminMonthlyAttendanceController extends Controller
{
     // /admin/attendance/month/{user}?month=2023-06
    public function index(Request $request, User $user)
    {
        // 月取得（YYYY-MM）
        $month = $request->query('month');

        $base = $month
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : Carbon::today()->startOfMonth();

        $start = $base->copy()->startOfMonth();
        $end   = $base->copy()->endOfMonth();

        $prevMonth = $base->copy()->subMonth()->format('Y-m');
        $nextMonth = $base->copy()->addMonth()->format('Y-m');
        $monthLabel = $base->format('Y/m');

        // 対象ユーザーのその月の勤怠
        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->work_date)->toDateString());

        $rows = [];

        $day = $start->copy();
        while ($day->lte($end)) {

            $key = $day->toDateString();
            $a = $attendances->get($key);

            $clockIn  = $a?->clock_in  ? Carbon::parse($a->clock_in)->format('H:i') : '';
            $clockOut = $a?->clock_out ? Carbon::parse($a->clock_out)->format('H:i') : '';

            // 休憩合計
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

            // 合計
            $totalText = '';
            if ($a && $a->clock_in && $a->clock_out) {
                $workMinutes = Carbon::parse($a->clock_in)
                    ->diffInMinutes(Carbon::parse($a->clock_out));

                $net = max(0, $workMinutes - $breakMinutes);

                $totalText = sprintf('%d:%02d', intdiv($net, 60), $net % 60);
            }

            $rows[] = [
                'date_label' => $day->format('m/d')
                    . '(' . ['日','月','火','水','木','金','土'][$day->dayOfWeek] . ')',
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'break' => $breakText,
                'total' => $totalText,
                'attendance_id' => $a?->id,
            ];
            $day->addDay();
        }

        return view('admin.attendance.staff', compact(
            'user',
            'rows',
            'prevMonth',
            'nextMonth',
            'monthLabel',
            'base'
        ));
    }
}

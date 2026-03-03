<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminStaffAttendanceController extends Controller
{
    // /admin/attendance/staff/{user}?month=2023-06
    public function index(Request $request, User $user)
    {
        $month = $request->query('month'); // "YYYY-MM"
        $base = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
        : Carbon::today()->startOfMonth();

        $start = $base->copy()->startOfMonth();
        $end   = $base->copy()->endOfMonth();

        $prevMonth = $base->copy()->subMonth()->format('Y-m');
        $nextMonth = $base->copy()->addMonth()->format('Y-m');
        $monthLabel = $base->format('Y/m');

        // 対象月の勤怠をまとめて取得（このユーザー分）
        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->work_date)->toDateString());

        // 1日〜末日まで行を作る（勤怠が無い日は空欄）
        $rows = [];
        $day = $start->copy();
        while ($day->lte($end)) {
            $key = $day->toDateString();
            $a = $attendances->get($key);

            $clockIn  = $a?->clock_in  ? Carbon::parse($a->clock_in)->format('H:i') : '';
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
                'date_label' => $day->format('m/d') . '(' . ['日','月','火','水','木','金','土'][$day->dayOfWeek] . ')',
                'clock_in' => $clockIn,
                'clock_out' => $clockOut,
                'break' => $breakText,
                'total' => $totalText,
                'attendance_id' => $a?->id, // 無ければ null
            ];
            $day->addDay();
        }
        return view('admin.attendance.staff', compact(
            'user', 'rows', 'prevMonth', 'nextMonth', 'monthLabel', 'base'
        ));

    }

    // CSV出力：/admin/attendance/staff/{user}/csv?month=2023-06
    public function csv(Request $request, User $user): StreamedResponse
    {
        $month = $request->query('month');
        $base = $month ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
        : Carbon::today()->startOfMonth();

        $start = $base->copy()->startOfMonth();
        $end   = $base->copy()->endOfMonth();

        $attendances = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($a) => Carbon::parse($a->work_date)->toDateString());

        $fileName = sprintf('%s_%s.csv', $user->name, $base->format('Y-m'));

        return response()->streamDownload(function () use ($start, $end, $attendances) {
            $out = fopen('php://output', 'w');

            // Excel文字化け対策
            // fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, ['日付', '出勤', '退勤', '休憩', '合計']);

            $day = $start->copy();
            while ($day->lte($end)) {
                $key = $day->toDateString();
                $a = $attendances->get($key);

            $clockIn  = $a?->clock_in  ? Carbon::parse($a->clock_in)->format('H:i') : '';
                $clockOut = $a?->clock_out ? Carbon::parse($a->clock_out)->format('H:i') : '';

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

                $totalText = '';
                if ($a && $a->clock_in && $a->clock_out) {
                    $workMinutes = Carbon::parse($a->clock_in)->diffInMinutes(Carbon::parse($a->clock_out));
                    $net = max(0, $workMinutes - $breakMinutes);
                    $totalText = sprintf('%d:%02d', intdiv($net, 60), $net % 60);
                }

                fputcsv($out, [$day->format('Y/m/d'), $clockIn, $clockOut, $breakText, $totalText]);
                $day->addDay();
            }
            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8',
        ]);

    }
}

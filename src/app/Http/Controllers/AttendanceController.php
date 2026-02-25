<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        // 出勤前（勤務外）として固定で表示（まずはデザイン確認用）
        $statusLabel = '勤務外';

        // 表示用（スクショの形式に寄せる）
        $now = Carbon::now();
        $dateText = $now->translatedFormat('Y年n月j日(D)');
        $timeText = $now->format('H:i');

        return view('attendance.index', compact('statusLabel', 'dateText', 'timeText'));
    }
}

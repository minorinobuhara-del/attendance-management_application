<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceRequest;

class RequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $pending = AttendanceRequest::with(['user','attendance'])
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $approved = AttendanceRequest::with(['user','attendance'])
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->latest()
            ->get();

        return view('requests.index', compact('pending', 'approved'));
    }

    public function show(AttendanceRequest $request)
    {
        // とりあえず勤怠詳細へ飛ばす
        $date = $request->attendance->work_date;
        return redirect()->route('attendance.detail', ['date' => $date]);
    }
}

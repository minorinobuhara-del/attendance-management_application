<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceRequest;
use App\Http\Requests\AttendanceUpdateRequest;

class AttendanceDetailController extends Controller
{
    public function show(int $id)
    {
    $user = Auth::user();

    $attendance = Attendance::with('breaks')
        ->where('user_id', Auth::id()) // 他人の勤怠を見れない
        ->findOrFail($id);

    $pendingRequest = AttendanceRequest::where('attendance_id', $attendance->id)
        ->where('status', 'pending')
        ->latest()
        ->first();

    return view('attendance.detail', compact('attendance', 'pendingRequest'));
    }

    public function requestUpdate(AttendanceUpdateRequest $request, int $id)
    {
    $user = Auth::user();

    $attendance = Attendance::with('breaks')
        ->where('user_id', Auth::id())
        ->findOrFail($id);

    $exists = AttendanceRequest::where('attendance_id', $attendance->id)
        ->where('status', 'pending')
        ->exists();

    if ($exists) {
        return back()->withErrors(['request' => '承認待ちのため修正はできません。']);
    }

    AttendanceRequest::create([
        'attendance_id' => $attendance->id,
        'user_id' => Auth::id(),
        'status' => 'pending',
        'payload' => $request->validated(),
    ]);

    // 申請一覧URLに合わせる
    return redirect()->route('stamp_correction_request.list')
        ->with('message', '修正申請を送信しました。');
    }

}

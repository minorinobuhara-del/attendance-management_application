<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceRequest;

class StampCorrectionRequestController extends Controller
{
    //一覧
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

        return view('stamp_correction_request.list',
            compact('pending', 'approved')
        );
    }

    //詳細
    public function show($attendance_correct_request_id)
    {
    $request = AttendanceRequest::with('attendance.user')
        ->findOrFail($attendance_correct_request_id);

    return view('stamp_correction_request.show', compact('request'));
    }
}

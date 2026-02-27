<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AttendanceRequest;

class StampCorrectionRequestController extends Controller
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

        return view('stamp_correction_request.list',
            compact('pending', 'approved')
        );
    }
}

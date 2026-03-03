<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;

class AdminStaffController extends Controller
{
    public function index()
    {
        // 全一般ユーザー
        $users = User::query()
            ->select('id', 'name', 'email')
            ->orderBy('id')
            ->get();

        return view('admin.staff.list', compact('users'));
    }
}

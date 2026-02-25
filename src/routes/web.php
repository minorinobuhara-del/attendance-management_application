<?php

use Illuminate\Support\Facades\Route;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
    //return view('welcome');
//});

//メール認証
Route::get('/email/verify', function () {
    return view('auth.verify-notice');
})->middleware('auth')->name('verification.notice');

Route::get('/home', fn () => redirect('/attendance'));

// 勤怠登録画面
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
});


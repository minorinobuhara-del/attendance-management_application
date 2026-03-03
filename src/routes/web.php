<?php

use Illuminate\Support\Facades\Route;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\StampCorrectionRequestController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminStaffController;

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

//ステータス確認(出勤・退勤・休憩開始・休憩終了)

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');//出勤
    Route::post('/attendance/break-in', [AttendanceController::class, 'breakIn'])->name('attendance.breakIn');//休憩開始
    Route::post('/attendance/break-out', [AttendanceController::class, 'breakOut'])->name('attendance.breakOut');//休憩終了
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');//退勤
    Route::get('/attendance/list', [AttendanceListController::class, 'index'])->name('attendance.list');//勤怠一覧（一般ユーザー）
    Route::get('/attendance/detail/{id}', [AttendanceDetailController::class, 'show'])->name('attendance.detail'); // 勤怠詳細(一般ユーザー)
    Route::post('/attendance/detail/{id}', [AttendanceDetailController::class, 'requestUpdate'])->name('attendance.detail.request');//勤怠修正申請
    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'index'])
        ->name('stamp_correction_request.list');//勤怠修正申請一覧
});

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->middleware(['fortify.admin', 'guest:admin'])
        ->name('login');//管理者ログイン画面

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(['fortify.admin', 'guest:admin', 'throttle:login'])
        ->name('login.store');//管理者ログイン処理

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware(['fortify.admin', 'auth:admin'])
        ->name('logout');//管理者ログアウト処理

    // --- 管理画面（勤怠） ---
    Route::middleware(['use_admin_fortify', 'auth:admin'])->group(function () {

        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
            ->name('attendance.list');//勤怠一覧（管理者）

        Route::get('/attendance/{attendance}', [AdminAttendanceController::class, 'show'])
            ->name('attendance.show');//勤怠詳細（管理者）

        Route::put('/attendance/{attendance}', [AdminAttendanceController::class, 'update'])
        ->name('attendance.update');//勤怠更新（管理者）

        Route::get('/staff/list', [AdminStaffController::class, 'index'])
        ->name('staff.list'); // スタッフ一覧(管理者)

        // 「月次勤怠（詳細）」遷移先（後で作成）
        Route::get('/attendance/month/{user}', [\App\Http\Controllers\Admin\AdminMonthlyAttendanceController::class, 'index'])
        ->name('attendance.month');
    });
});

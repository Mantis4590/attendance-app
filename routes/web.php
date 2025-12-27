<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AdminStampCorrectionRequestController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::get('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth', 'verified')->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
    ->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
    ->name('attendance.clockOut');
    Route::post('/attendance/start-break', [AttendanceController::class, 'startBreak'])
    ->name('attendance.startBreak');
    Route::post('/attendance/end-break', [AttendanceController::class, 'endBreak'])
    ->name('attendance.endBreak');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route:: get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
    ->name('attendance.detail');
    Route::post('/attendance/update/{id}', [AttendanceController::class, 'update'])
        ->name('attendance.update');
});

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    return redirect()->route('attendance.index');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', '認証メールを再送しました');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])
    ->name('request.list');
Route::post('/logout', function() {
    Auth::logout();
    return redirect('/login');
})->name('logout');
Route::post('/register', [AuthController::class, 'store'])->name('register.store');
Route::post('/login', [AuthController::class, 'loginStore'])->name('login.store');
Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])
    ->name('request.list');
Route::get('/admin/login', [AdminAuthController::class, 'login'])
->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'loginStore'])
->name('admin.login.store');
Route::prefix('admin')->middleware('auth:admin')->group(function () {
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('admin.attendance.list');
    Route::get('/stamp_correction_request/list', [AdminStampCorrectionRequestController::class, 'index'])
        ->name('admin.stamp_correction_request.list');
    Route::post('/stamp_correction_request/approve/{stampCorrectionRequest}',
    [AdminStampCorrectionRequestController::class, 'approve'])->name('admin.stamp_correction_request.approve');
});

Route::post('/admin/logout', function () {
    Auth::guard('admin')->logout();
    return redirect()->route('admin.login');
})->name('admin.logout');

Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])
    ->name('admin.attendance.show');
Route::patch('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])
    ->name('admin.attendance.update');

Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])
        ->name('admin.staff');
    Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'staff'])
        ->name('admin.attendance.staff');
    Route::get(
        '/stamp_correction_request/approve/{stampCorrectionRequest}',
        [AdminStampCorrectionRequestController::class, 'show']
    )->name('admin.stamp_correction_request.show');
    Route::get(
        '/admin/attendance/staff/{id}/csv', [AdminAttendanceController::class, 'csv']
    )->name('admin.attendance.staff.csv');
});

Route::post('/attendance/{attendance}/request',
[AttendanceController::class, 'store'])->name('attendance.request');
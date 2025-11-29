<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', [AuthController::class, 'register'])->name('register');
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
Route:: get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
    ->name('attendance.detail');
Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])
    ->name('request.list');
Route::post('/logout', function() {
    Auth::logout();
    return redirect('/login');
})->name('logout');
Route::post('/register', [AuthController::class, 'store'])->name('register.store');
Route::post('/login', [AuthController::class, 'loginStore'])->name('login.store');
Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])
    ->name('attendance.clockIn');

Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])
    ->name('attendance.clockOut');

Route::post('/attendance/start-break', [AttendanceController::class, 'startBreak'])
    ->name('attendance.startBreak');

Route::post('/attendance/end-break', [AttendanceController::class, 'endBreak'])
    ->name('attendance.endBreak');
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminCorrectionController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/register', fn () => view('auth.register'))->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', fn () => view('auth.login'))->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('validate.login')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');
    Route::post('/attendance/break/start', [AttendanceController::class, 'breakStart'])->name('attendance.break_start');
    Route::post('/attendance/break/end', [AttendanceController::class, 'breakEnd'])->name('attendance.break_end');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');

    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])
        ->name('stamp_correction_request.list');

    Route::post('/stamp_correction_request', [StampCorrectionRequestController::class, 'store'])
        ->name('stamp_correction_request.store');

    Route::get('/stamp_correction_request/{id}', [StampCorrectionRequestController::class, 'show'])
        ->whereNumber('id')
        ->name('stamp_correction_request.show');
});

Route::prefix('admin')->name('admin.')->middleware('fortify.admin')->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', fn () => view('admin.login'))->name('login');
        Route::post('/login', [AuthenticatedSessionController::class, 'store'])
            ->middleware('validate.login')
            ->name('login.store');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');

        Route::get('/stamp_correction_requests', [AdminCorrectionController::class, 'index'])
            ->name('stamp_correction_requests.index');

        Route::get('/stamp_correction_requests/{id}', [AdminCorrectionController::class, 'show'])
            ->whereNumber('id')
            ->name('stamp_correction_requests.show');

        Route::post('/stamp_correction_requests/{id}/approve', [AdminCorrectionController::class, 'approve'])
            ->whereNumber('id')
            ->name('stamp_correction_requests.approve');

        Route::post('/stamp_correction_requests/{id}/reject', [AdminCorrectionController::class, 'reject'])
            ->whereNumber('id')
            ->name('stamp_correction_requests.reject');
    });
});
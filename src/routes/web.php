<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\StampCorrectionRequestController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\AdminCorrectionController;
use App\Http\Controllers\Admin\AdminStaffController;
use App\Http\Controllers\Admin\AdminAuthController;
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
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');
    Route::post('/attendance/break/start', [AttendanceController::class, 'breakStart'])->name('attendance.break_start');
    Route::post('/attendance/break/end', [AttendanceController::class, 'breakEnd'])->name('attendance.break_end');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])
        ->whereNumber('id')
        ->name('attendance.detail');

    Route::get('/stamp_correction_request/list', [StampCorrectionRequestController::class, 'list'])
        ->name('stamp_correction_request.list');

    Route::post('/stamp_correction_request', [StampCorrectionRequestController::class, 'store'])
        ->name('stamp_correction_request.store');

    Route::get('/stamp_correction_request/{id}', [StampCorrectionRequestController::class, 'show'])
        ->whereNumber('id')
        ->name('stamp_correction_request.show');
});

Route::prefix('admin')->name('admin.')->middleware('fortify.admin')->group(function () {
    Route::get('/', function () {
        return auth('admin')->check()
            ? redirect()->route('admin.attendance.list')
            : redirect()->route('admin.login.form');
    })->name('root');

    Route::middleware('guest:admin')->group(function () {
        Route::get('/login', fn () => view('admin.login'))->name('login.form');

        Route::post('/login', [AuthenticatedSessionController::class, 'store'])
            ->middleware('validate.login')
            ->name('login.store');
    });

    Route::middleware('auth:admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/attendance/list', [AdminAttendanceController::class, 'daily'])->name('attendance.list');

        Route::get('/attendance/{id}', [AdminAttendanceController::class, 'detail'])
            ->whereNumber('id')
            ->name('attendance.detail');

        Route::patch('/attendance/{id}', [AdminAttendanceController::class, 'update'])
            ->whereNumber('id')
            ->name('attendance.update');

        Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'monthly'])
            ->whereNumber('id')
            ->name('attendance.staff');

        Route::get('/attendance/staff/{id}/export', [AdminAttendanceController::class, 'exportCsv'])
            ->whereNumber('id')
            ->name('attendance.export');

        Route::get('/stamp_correction_request/list', [AdminCorrectionController::class, 'index'])
            ->name('stamp_correction_request.list');

        Route::get('/stamp_correction_request/{attendance_correct_request_id}', [AdminCorrectionController::class, 'show'])
            ->whereNumber('attendance_correct_request_id')
            ->name('stamp_correction_request.show');

        Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminCorrectionController::class, 'approveForm'])
            ->whereNumber('attendance_correct_request_id')
            ->name('stamp_correction_request.approve.form');

        Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminCorrectionController::class, 'approve'])
            ->whereNumber('attendance_correct_request_id')
            ->name('stamp_correction_request.approve');

        Route::post('/stamp_correction_request/reject/{attendance_correct_request_id}', [AdminCorrectionController::class, 'reject'])
            ->whereNumber('attendance_correct_request_id')
            ->name('stamp_correction_request.reject');

        Route::get('/staff/list', [AdminStaffController::class, 'list'])->name('staff.list');
    });
});
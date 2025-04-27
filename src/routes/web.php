<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AttendanceRequestController as AdminAttendanceRequestController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// =====================
// 一般ユーザー向けページ
// =====================

// 会員登録
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register.show');
Route::post('/register', [AuthController::class, 'store'])->name('register.store');

// ログイン
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.show');
Route::post('/login', [AuthController::class, 'login'])->name('login');

// ログアウト
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// =====================
// 認証が必要な一般ユーザー機能
// =====================
Route::middleware(['auth'])->group(function () {
    // 勤怠打刻
    Route::get('/attendance', [AttendanceController::class, 'show'])->name('attendance.show');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.list');

    // 勤怠詳細・修正申請
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetail'])->name('attendance.detail');
    Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');

    // 自分の修正申請一覧
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'index'])->name('request.list');
});

// =====================
// 管理者向けページ
// =====================

// 管理者ログイン
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login.show');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login');

// =====================
// 認証が必要な管理者機能
// =====================
Route::middleware(['auth:admin', 'admin'])->group(function () {
    // 管理者による勤怠一覧
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');

    // 勤怠詳細確認・修正
    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.detail');
    Route::put('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

    // スタッフ一覧
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.list');

    // スタッフ別月次勤怠一覧
    Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'staffMonthlyList'])->name('admin.attendance.staff');

    // CSV出力
    Route::get('/admin/attendance/staff/{id}/export', [AdminAttendanceController::class, 'exportMonthlyCsv'])->name('admin.attendance.staff.export');

    // 修正申請一覧
    Route::get('/admin/stamp_correction_request/list', [AdminAttendanceRequestController::class, 'index'])->name('admin.request.list');

    // 修正申請詳細表示（承認ページ）
    Route::get('/stamp_correction_request/approve/{id}', [AdminAttendanceRequestController::class, 'show'])->name('admin.request.show');

    // 修正申請の承認処理（POST）
    Route::post('/stamp_correction_request/approve/{id}', [AdminAttendanceRequestController::class, 'approve'])->name('admin.request.approve');
});

// メール認証関連のルート
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// メール認証を完了したら、自動ログインしプロフィール設定へ
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $user = $request->user();
    $request->fulfill(); // 認証を完了

    Auth::login($user); // 認証完了したら自動ログイン

    return redirect('/attendance'); // 勤怠登録画面へリダイレクト
})->middleware(['auth', 'signed'])->name('verification.verify');

// メール認証の再送信
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('resent', true);
})->middleware(['auth'])->name('verification.resend');

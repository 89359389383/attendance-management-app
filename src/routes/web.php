<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceRequestController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AttendanceRequestController as AdminAttendanceRequestController;
use App\Http\Controllers\Admin\StaffController as AdminStaffController;

// =====================
// 一般ユーザー向けページ
// =====================

// 会員登録画面（GET）・登録処理（POST）
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register.show'); // 会員登録フォームを表示
Route::post('/register', [AuthController::class, 'store'])->name('register.store');           // 会員登録処理

// ログイン画面（GET）・ログイン処理（POST）
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.show'); // ログインフォームを表示
Route::post('/login', [AuthController::class, 'login'])->name('login');             // ログイン処理

// ログアウト処理（POST）
Route::post('/logout', [AuthController::class, 'logout'])->name('logout'); // ログアウト処理


// =====================
// 認証が必要な一般ユーザー機能
// =====================
Route::middleware(['auth'])->group(function () {

    // 出勤登録画面（GET）・打刻処理（POST）
    Route::get('/attendance', [AttendanceController::class, 'show'])->name('attendance.show'); // 勤怠打刻画面を表示
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store'); // 勤怠打刻処理

    // 勤怠一覧（GET）
    Route::get('/attendance/list', [AttendanceController::class, 'index'])->name('attendance.list'); // 自身の勤怠一覧を表示

    // 勤怠詳細（GET）・修正申請（PUT）
    Route::get('/attendance/{id}', [AttendanceController::class, 'showDetail'])->name('attendance.detail'); // 勤怠詳細の確認
    Route::put('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');     // 勤怠修正申請処理

    // 自分の修正申請一覧（GET）
    Route::get('/stamp_correction_request/list', [AttendanceRequestController::class, 'index'])->name('request.list'); // 修正申請一覧

});


// =====================
// 管理者向けページ
// =====================

// 管理者ログイン画面（GET）・ログイン処理（POST）
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login.show'); // 管理者ログインフォーム
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login');             // 管理者ログイン処理

// =====================
// 認証が必要な管理者機能
// =====================
Route::middleware(['auth', 'admin'])->group(function () {

    // 管理者による勤怠一覧（全ユーザー）
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list'); // 管理者の勤怠一覧表示

    // 管理者による勤怠詳細確認・修正（GET/PUT）
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.detail'); // 勤怠詳細確認
    Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update'); // 勤怠修正処理

    // スタッフ一覧
    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])->name('admin.staff.list'); // 全一般ユーザー一覧

    // スタッフごとの月次勤怠一覧
    Route::get('/admin/attendance/staff/{id}', [AdminAttendanceController::class, 'staffMonthlyList'])->name('admin.attendance.staff'); // スタッフ別勤怠月次一覧

    // 修正申請一覧（全ユーザー）
    Route::get('/stamp_correction_request/list', [AdminAttendanceRequestController::class, 'index'])->name('admin.request.list'); // 修正申請一覧

    // 修正申請の承認処理（POST）
    Route::post('/stamp_correction_request/approve/{id}', [AdminAttendanceRequestController::class, 'approve'])->name('admin.request.approve'); // 承認処理
});

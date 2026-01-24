<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;

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


// 開発用表示ルート Route::view('URI', 'ビュー名')

// 開発用表示ルート 一般ユーザー
Route::view('/register', 'auth.register');
// Route::view('/login', 'auth.login')->name('login');
Route::view('/verify', 'auth.verify-email');

// attendanceディレクトリ内のファイルを参照
// Route::view('/attendance', 'attendance.index');
// Route::view('/attendance/list', 'attendance.list');
// Route::view('/attendance/detail/{id}', 'attendance.detail');

// requestディレクトリ内のファイルを参照
// Route::view('/stamp_correction_request/list', 'request.list'); // 修正申請一覧

// 開発用表示ルート 管理者 (adminディレクトリ内を参照)
// Route::view('/admin/login', 'admin.login');
Route::view('/admin/attendance/list', 'admin.attendance.list');
Route::view('/admin/attendance/{id}', 'admin.attendance.detail');
Route::view('/admin/staff/list', 'admin.staff.list');
Route::view('/admin/attendance/staff/{id}', 'admin.staff.attendance_list');

// 管理者用の修正申請 (admin/requestディレクトリ内)
Route::view('/admin/stamp_correction_request/list', 'admin.request.list');
Route::view('/admin/stamp_correction_request/approve/{attendance_correct_request_id}', 'admin.request.approve');


// 一般ユーザーと管理者で同じURLを使用する場合
// Route::get('/stamp_correction_request/list', function () {
    // 例：ログインユーザーが管理者なら admin 用の blade を返す
    // if (auth()->user() && auth()->user()->is_admin) {
    //     return view('admin.request.list');
    // }
    // そうでなければ一般ユーザー用の blade を返す
    // return view('request.list');
// });


// --- 認証画面の定義 (Authenticate.phpのリダイレクト先として必要) ---

// 一般ユーザー用ログイン画面
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// 管理者用ログイン画面
Route::get('/admin/login', function () {
    return view('admin.login');
})->name('admin.login');


// --- ログイン済みユーザーのみアクセス可能（一般・管理者共通） ---
Route::middleware(['auth'])->group(function () {

    // --- スタッフ用：勤怠管理 ---
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/start', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/end', [AttendanceController::class, 'checkOut']);
    Route::post('/attendance/rest-start', [AttendanceController::class, 'restStart']);
    Route::post('/attendance/rest-end', [AttendanceController::class, 'restEnd']);

    // 自分の勤怠一覧 (FN023, FN024)
    Route::get('/attendance/list', [AttendanceController::class, 'attendanceList'])->name('attendance.list');

    // 勤怠詳細（修正申請フォーム）の表示 (FN026)
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'showDetail'])->name('attendance.detail');

    // 修正申請の保存処理 (FN030)
    Route::post('/attendance/detail/{id}', [AttendanceController::class, 'updateRequest'])->name('attendance.update');

    // 自分の申請一覧画面を表示 (FN031, FN032)
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'requestList'])->name('request.list');


    // 管理者専用ルートのグループ（後ほど role で判定）
    // ❗ middleware に 'admin' を追加することで、一般ユーザーをブロック
    Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {

        // 全スタッフの当日勤怠一覧 (FN034, FN035)
        Route::get('/attendance/list', [AdminController::class, 'dailyAttendance'])->name('admin.attendance.list');

        // 全スタッフ一覧を表示 (FN041, FN042)
        Route::get('/staff/list', [AdminController::class, 'staffList'])->name('admin.staff.list');

        // 特定スタッフの月次勤怠を表示 (FN043, FN044)
        Route::get('/attendance/staff/{id}', [AdminController::class, 'staffAttendance'])->name('admin.staff.attendance');

        // 修正申請一覧の表示 (FN047, FN048)
        Route::get('/stamp_correction_request/list', [AdminController::class, 'requestList'])->name('admin.request.list');

        // 修正申請の承認処理 (FN051)
        // ※詳細画面から「承認」ボタンを押した際の保存アクション
        Route::post('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminController::class, 'approveRequest'])->name('admin.request.approve');

        // 申請詳細の確認画面 (FN050)
        Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminController::class, 'showApproveDetail'])->name('admin.request.show');
    });
});

<?php

use Illuminate\Support\Facades\Route;

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
Route::view('/login', 'auth.login');
Route::view('/verify', 'auth.verify-email');

// attendanceディレクトリ内のファイルを参照
Route::view('/attendance', 'attendance.index');
Route::view('/attendance/list', 'attendance.list');
Route::view('/attendance/detail/{id}', 'attendance.detail');

// requestディレクトリ内のファイルを参照
Route::view('/stamp_correction_request/list', 'request.list'); // 修正申請一覧

// 開発用表示ルート 管理者 (adminディレクトリ内を参照)
Route::view('/admin/login', 'admin.login');
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

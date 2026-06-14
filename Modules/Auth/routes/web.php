<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\MeController;

/*
|--------------------------------------------------------------------------
| Auth Module — Web Routes
|--------------------------------------------------------------------------
| Fortify đã đăng ký sẵn: GET/POST /login, POST /logout,
| GET/POST /register, GET/POST /forgot-password, POST /reset-password.
| Fortify cũng đăng ký: PUT /user/profile-information, PUT /user/password.
|
| File này bổ sung: /home redirect, /auth/me debug, /auth/profile.
*/

Route::get('/home', function () {
    return redirect('/');
})->middleware('auth')->name('home');

Route::middleware(['auth'])->prefix('auth')->name('auth.')->group(function () {

    // Profile page — xem và cập nhật thông tin cá nhân
    Route::get('/profile', fn (Request $request) => view('auth::profile', [
        'user' => $request->user(),
    ]))->name('profile');

    // Context endpoint: trả về user/org/roles của chính mình.
    // permissions chỉ hiện với System_Admin.
    Route::get('/me', MeController::class)->name('me');

});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Shared\Tenancy\TenantContext;

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

    // Debug endpoint: xem user + tenant + RBAC đang hoạt động
    Route::middleware('tenant')->get('/me', function (Request $request) {
        $user = $request->user()->load('organization');

        return response()->json([
            'user' => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'organization_id' => $user->organization_id,
            ],
            'organization' => TenantContext::get()?->only([
                'id', 'name', 'slug', 'status',
            ]),
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name')->sort()->values(),
        ]);
    })->name('me');

});

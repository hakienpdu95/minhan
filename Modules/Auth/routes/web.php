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
|
| File này chỉ bổ sung /home redirect và /auth/me debug endpoint.
*/

Route::get('/home', function () {
    return redirect('/');
})->middleware('auth')->name('home');

// Debug endpoint: xem user + tenant + RBAC đang hoạt động
Route::middleware(['auth', 'tenant'])->prefix('auth')->name('auth.')->group(function () {

    Route::get('/me', function (Request $request) {
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

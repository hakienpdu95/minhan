<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| User Module — Web Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('dashboard')->name('backend.')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
});

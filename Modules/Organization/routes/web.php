<?php

use Illuminate\Support\Facades\Route;
use Modules\Organization\Http\Controllers\Api\OrganizationApiController;
use Modules\Organization\Http\Controllers\OrganizationController;

/*
|--------------------------------------------------------------------------
| Organization Module — Web Routes
|--------------------------------------------------------------------------
*/

// ── Backend CRUD (admin panel — System_Admin / super-admin only) ───────────
// Authorization được enforce qua OrganizationPolicy::authorizeResource() trong constructor.
Route::middleware(['auth'])->prefix('dashboard')->name('backend.')->group(function () {
    Route::resource('organizations', OrganizationController::class);
});

// ── Backend JSON API for Tabulator (session-based auth, same guard as admin panel) ─────
Route::middleware(['auth'])->prefix('backend/api')->name('backend.api.')->group(function () {
    Route::get('organizations', [OrganizationApiController::class, 'index'])
        ->name('organizations');
});

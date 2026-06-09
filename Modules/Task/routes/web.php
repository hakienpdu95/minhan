<?php

use Illuminate\Support\Facades\Route;
use Modules\Task\Http\Controllers\Api\TaskApiController;
use Modules\Task\Http\Controllers\TaskController;

/*
|--------------------------------------------------------------------------
| Task Module — Web Routes
|--------------------------------------------------------------------------
*/

// ── Backend CRUD ────────────────────────────────────────────────────────────
Route::middleware(['auth'])->prefix('dashboard')->name('backend.')->group(function () {
    Route::resource('tasks', TaskController::class);
});

// ── Backend JSON API for Tabulator ──────────────────────────────────────────
Route::middleware(['auth'])->prefix('backend/api')->name('backend.api.')->group(function () {
    Route::get('tasks', [TaskApiController::class, 'index'])->name('tasks');
});

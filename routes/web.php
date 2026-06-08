<?php

use App\Http\Controllers\Api\MediaJoditUploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('backend.dashboard'));

/*
|--------------------------------------------------------------------------
| Media API Routes — prefix: api/v1/media
|--------------------------------------------------------------------------
*/
Route::middleware(['api', 'auth:sanctum', 'tenant'])
    ->prefix('api/v1/media')
    ->name('api.media.')
    ->group(function () {
        Route::post('jodit-upload',         [MediaJoditUploadController::class, 'store'])->name('jodit.upload');
        Route::delete('jodit-upload/{uuid}',[MediaJoditUploadController::class, 'destroy'])->name('jodit.destroy');
        Route::post('jodit-discard',        [MediaJoditUploadController::class, 'discard'])->name('jodit.discard');
        Route::patch('jodit-touch',         [MediaJoditUploadController::class, 'touch'])->name('jodit.touch');
        Route::get('{uuid}/url',            [MediaJoditUploadController::class, 'refreshUrl'])->name('url.refresh');
    });

/*
|--------------------------------------------------------------------------
| Backend Routes — prefix: backend.*
|--------------------------------------------------------------------------
| Organization CRUD  → Modules/Organization/routes/web.php
| User CRUD          → Modules/User/routes/web.php
*/
Route::middleware(['auth'])->prefix('dashboard')->name('backend.')->group(function () {

    Route::get('/', fn () => view('backend.dashboard.index'))->name('dashboard');

    // ── Placeholder routes (modules chưa triển khai) ──────────────────
    Route::get('/products',         fn () => abort(503, 'Module đang phát triển'))->name('products.index');
    Route::get('/products/create',  fn () => abort(503, 'Module đang phát triển'))->name('products.create');
    Route::get('/orders',           fn () => abort(503, 'Module đang phát triển'))->name('orders.index');
    Route::get('/customers',        fn () => abort(503, 'Module đang phát triển'))->name('customers.index');
    Route::get('/customers/create', fn () => abort(503, 'Module đang phát triển'))->name('customers.create');
    Route::get('/categories',       fn () => abort(503, 'Module đang phát triển'))->name('categories.index');
    Route::get('/categories/create',fn () => abort(503, 'Module đang phát triển'))->name('categories.create');
    Route::get('/settings',         fn () => abort(503, 'Module đang phát triển'))->name('settings.index');
    Route::get('/reports',          fn () => abort(503, 'Module đang phát triển'))->name('reports.index');

});

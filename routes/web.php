<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('backend.dashboard'));

/*
|--------------------------------------------------------------------------
| Backend Routes — prefix: backend.*
|--------------------------------------------------------------------------
| Tất cả route đã được đặt tên theo convention backend.* để khớp với
| sidebar.blade.php và các partial views.
*/
Route::middleware(['auth'])->prefix('dashboard')->name('backend.')->group(function () {

    // Dashboard
    Route::get('/', fn () => view('backend.dashboard.index'))->name('dashboard');

    // ── Placeholder routes — sidebar links (chưa có module tương ứng) ──
    Route::get('/products',        fn () => abort(503, 'Module đang phát triển'))->name('products.index');
    Route::get('/products/create', fn () => abort(503, 'Module đang phát triển'))->name('products.create');

    Route::get('/orders',          fn () => abort(503, 'Module đang phát triển'))->name('orders.index');

    Route::get('/customers',       fn () => abort(503, 'Module đang phát triển'))->name('customers.index');
    Route::get('/customers/create',fn () => abort(503, 'Module đang phát triển'))->name('customers.create');

    Route::get('/categories',      fn () => abort(503, 'Module đang phát triển'))->name('categories.index');
    Route::get('/categories/create',fn () => abort(503, 'Module đang phát triển'))->name('categories.create');

    Route::get('/settings',        fn () => abort(503, 'Module đang phát triển'))->name('settings.index');
    Route::get('/reports',         fn () => abort(503, 'Module đang phát triển'))->name('reports.index');

});

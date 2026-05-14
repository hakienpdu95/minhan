<?php
// routes/backend.php
// Include this file from routes/web.php:
//
//   require base_path('routes/backend.php');
//

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\DashboardController;
use App\Http\Controllers\Backend\ProductController;
use App\Http\Controllers\Backend\OrderController;
use App\Http\Controllers\Backend\CustomerController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\SettingController;

Route::prefix('admin')
    ->name('backend.')
    ->middleware(['web'])
    ->group(function () {

        // ── Dashboard ──────────────────────────────────────────
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // ── Profile ────────────────────────────────────────────
        Route::get('/profile', fn () => view('backend.profile.index'))->name('profile');

        // ── Products ───────────────────────────────────────────
        Route::resource('products', ProductController::class);
        Route::get('products/trash', [ProductController::class, 'trash'])->name('products.trash');
        Route::patch('products/{product}/restore', [ProductController::class, 'restore'])->name('products.restore');
        Route::delete('products/{product}/force-delete', [ProductController::class, 'forceDelete'])->name('products.force-delete');

        // ── Orders ─────────────────────────────────────────────
        Route::resource('orders', OrderController::class)->except(['create', 'store']);
        Route::get('orders/pending',   [OrderController::class, 'pending'])->name('orders.pending');
        Route::get('orders/shipping',  [OrderController::class, 'shipping'])->name('orders.shipping');
        Route::get('orders/completed', [OrderController::class, 'completed'])->name('orders.completed');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');

        // ── Customers ──────────────────────────────────────────
        Route::resource('customers', CustomerController::class);

        // ── Categories ─────────────────────────────────────────
        Route::resource('categories', CategoryController::class);

        // ── Settings ───────────────────────────────────────────
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/',         [SettingController::class, 'index'])->name('index');
            Route::post('/',        [SettingController::class, 'update'])->name('update');
            Route::get('/payment',  [SettingController::class, 'payment'])->name('payment');
            Route::get('/shipping', [SettingController::class, 'shipping'])->name('shipping');
            Route::get('/email',    [SettingController::class, 'email'])->name('email');
        });
        // Alias for header profile link
        Route::get('/settings', [SettingController::class, 'index'])->name('settings');

    });
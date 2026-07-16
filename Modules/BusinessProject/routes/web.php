<?php

use Illuminate\Support\Facades\Route;
use Modules\BusinessProject\Http\Controllers\BusinessContextController;
use Modules\BusinessProject\Http\Controllers\BusinessProjectController;

// Chưa gắn middleware feature:module.businessproject (Subscription feature gate) ở
// Vertical Slice 1 — BCOS là công cụ nội bộ (Handbook: "không phải sản phẩm bán cho
// doanh nghiệp"), đăng ký feature flag cho Subscription module là việc riêng, ngoài
// phạm vi Vertical Slice 1.
Route::middleware(['auth', 'verified'])->prefix('dashboard/business-projects')->name('backend.business-projects.')->group(function () {
    Route::get('/', [BusinessProjectController::class, 'index'])->name('index');
    Route::get('/{businessProject}', [BusinessProjectController::class, 'show'])->name('show');
    Route::post('/{businessProject}/advance-stage', [BusinessProjectController::class, 'advanceStage'])->name('advance-stage');

    Route::prefix('/{businessProject}/context')->name('context.')->group(function () {
        Route::post('/', [BusinessContextController::class, 'store'])->name('store');
        Route::put('/', [BusinessContextController::class, 'update'])->name('update');
        Route::post('/submit', [BusinessContextController::class, 'submit'])->name('submit');
        Route::post('/approve', [BusinessContextController::class, 'approve'])->name('approve');
        Route::post('/reject', [BusinessContextController::class, 'reject'])->name('reject');
    });
});

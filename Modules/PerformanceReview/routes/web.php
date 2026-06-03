<?php

use Illuminate\Support\Facades\Route;
use Modules\PerformanceReview\Http\Controllers\PerformanceReviewController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('performancereviews', PerformanceReviewController::class)->names('performancereview');
});

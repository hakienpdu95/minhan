<?php

use Illuminate\Support\Facades\Route;
use Modules\PerformanceReview\Http\Controllers\PerformanceReviewController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('performancereviews', PerformanceReviewController::class)->names('performancereview');
});

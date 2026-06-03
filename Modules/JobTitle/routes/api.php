<?php

use Illuminate\Support\Facades\Route;
use Modules\JobTitle\Http\Controllers\JobTitleController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('jobtitles', JobTitleController::class)->names('jobtitle');
});

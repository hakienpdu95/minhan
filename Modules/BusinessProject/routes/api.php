<?php

use Illuminate\Support\Facades\Route;
use Modules\BusinessProject\Http\Controllers\BusinessProjectController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('businessprojects', BusinessProjectController::class)->names('businessproject');
});

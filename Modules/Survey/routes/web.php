<?php

use Illuminate\Support\Facades\Route;
use Modules\Survey\Http\Controllers\SurveyController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('surveys', SurveyController::class)->names('survey');
});

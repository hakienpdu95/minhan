<?php

use Illuminate\Support\Facades\Route;
use Modules\Survey\Http\Controllers\SurveyController;

Route::middleware(['api'])->prefix('v1')->name('surveys.')->group(function () {
    Route::get('surveys/{slug}/schema',    [SurveyController::class, 'schema'])->name('schema');
    Route::post('surveys/{slug}/submit',   [SurveyController::class, 'submit'])->name('submit');
    Route::get('surveys/{slug}/stats',     [SurveyController::class, 'stats'])->name('stats');
    Route::get('surveys/{slug}/responses', [SurveyController::class, 'responses'])->name('responses');
});

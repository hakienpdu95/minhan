<?php

use Illuminate\Support\Facades\Route;
use Modules\Survey\Http\Controllers\SurveyController;
use Modules\Survey\Http\Middleware\ValidateSurveyToken;
use Modules\Survey\Http\Middleware\ValidateSurveyTurnstile;

/*
|--------------------------------------------------------------------------
| Survey Module — Public API Routes
|--------------------------------------------------------------------------
| /schema  + /submit  → yêu cầu Bearer token (ValidateSurveyToken)
| /submit             → rate limit 60 req/min per token (survey-submit limiter)
| /stats  + /responses → không cần token (internal / reporting)
*/

// middleware('api') đã được RouteServiceProvider gắn — không cần khai báo lại
Route::prefix('v1')->name('surveys.')->group(function () {

    // ── Token-protected endpoints ──────────────────────────────────────
    Route::middleware(ValidateSurveyToken::class)->group(function () {
        Route::get('surveys/{slug}/schema', [SurveyController::class, 'schema'])->name('schema');

        Route::middleware(['throttle:survey-submit', ValidateSurveyTurnstile::class])
            ->post('surveys/{slug}/submit', [SurveyController::class, 'submit'])
            ->name('submit');

        // stats + responses chứa data nhạy cảm → yêu cầu token
        Route::get('surveys/{slug}/stats',     [SurveyController::class, 'stats'])->name('stats');
        Route::get('surveys/{slug}/responses', [SurveyController::class, 'responses'])->name('responses');
    });

});

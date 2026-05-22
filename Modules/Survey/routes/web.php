<?php

use Illuminate\Support\Facades\Route;
use Modules\Survey\Http\Controllers\Api\SurveyBackendApiController;
use Modules\Survey\Http\Controllers\FieldController;
use Modules\Survey\Http\Controllers\OptionController;
use Modules\Survey\Http\Controllers\ResponseController;
use Modules\Survey\Http\Controllers\SectionController;
use Modules\Survey\Http\Controllers\StatsController;
use Modules\Survey\Http\Controllers\SurveyController;
use Modules\Survey\Http\Controllers\TokenController;

/*
|--------------------------------------------------------------------------
| Survey Module — Backend Admin Routes
|--------------------------------------------------------------------------
| Web CRUD:  prefix = dashboard/surveys  → name = backend.surveys.*
| Builder JSON API: trả JSON, dùng bởi Alpine.js fetch() trong builder.
*/

Route::middleware(['auth'])->prefix('dashboard')->name('backend.')->group(function () {

    // ── Survey CRUD ────────────────────────────────────────────────────
    Route::prefix('surveys')->name('surveys.')->group(function () {
        Route::get('/',                   [SurveyController::class, 'index'])->name('index');
        Route::get('/create',             [SurveyController::class, 'create'])->name('create');
        Route::post('/',                  [SurveyController::class, 'store'])->name('store');
        Route::get('/{survey}/edit',      [SurveyController::class, 'edit'])->name('edit');
        Route::put('/{survey}',           [SurveyController::class, 'update'])->name('update');
        Route::delete('/{survey}',        [SurveyController::class, 'destroy'])->name('destroy');
        Route::post('/{survey}/activate', [SurveyController::class, 'activate'])->name('activate');

        // ── Token management ───────────────────────────────────────────
        Route::prefix('/{survey}/tokens')->name('tokens.')->group(function () {
            Route::get('/',                    [TokenController::class, 'index'])->name('index');
            Route::post('/',                   [TokenController::class, 'store'])->name('store');
            Route::get('/{token}/reveal',      [TokenController::class, 'reveal'])->name('reveal');
            Route::patch('/{token}/revoke',    [TokenController::class, 'revoke'])->name('revoke');
            Route::delete('/{token}',          [TokenController::class, 'destroy'])->name('destroy');
        });

        // ── Stats dashboard ────────────────────────────────────────────
        Route::get('/{survey}/stats', [StatsController::class, 'index'])->name('stats.index');

        // ── Response management + export ───────────────────────────────
        Route::prefix('/{survey}/responses')->name('responses.')->group(function () {
            Route::get('/',              [ResponseController::class, 'index'])->name('index');
            Route::get('/export',        [ResponseController::class, 'export'])->name('export');
            Route::get('/{response}',    [ResponseController::class, 'show'])->name('show');
            Route::delete('/{response}', [ResponseController::class, 'destroy'])->name('destroy');
        });

        // ── Section builder (JSON) ─────────────────────────────────────
        Route::prefix('/{survey}/sections')->name('sections.')->group(function () {
            Route::post('/',               [SectionController::class, 'store'])->name('store');
            Route::put('/{section}',       [SectionController::class, 'update'])->name('update');
            Route::delete('/{section}',    [SectionController::class, 'destroy'])->name('destroy');
            Route::patch('/reorder',       [SectionController::class, 'reorder'])->name('reorder');
        });

        // ── Field builder (JSON) ───────────────────────────────────────
        Route::prefix('/{survey}/fields')->name('fields.')->group(function () {
            Route::post('/',                [FieldController::class, 'store'])->name('store');
            Route::put('/{field}',          [FieldController::class, 'update'])->name('update');
            Route::delete('/{field}',       [FieldController::class, 'destroy'])->name('destroy');
            Route::patch('/{field}/toggle', [FieldController::class, 'toggleActive'])->name('toggle');
            Route::patch('/reorder',        [FieldController::class, 'reorder'])->name('reorder');

            // ── Option builder (JSON) ──────────────────────────────────
            Route::prefix('/{field}/options')->name('options.')->group(function () {
                Route::post('/',             [OptionController::class, 'store'])->name('store');
                Route::put('/{option}',      [OptionController::class, 'update'])->name('update');
                Route::delete('/{option}',   [OptionController::class, 'destroy'])->name('destroy');
                Route::patch('/reorder',     [OptionController::class, 'reorder'])->name('reorder');
            });
        });
    });

});

// ── Backend JSON API for Tabulator (session-based auth) ───────────────────
Route::middleware(['auth'])->prefix('backend/api')->name('backend.api.')->group(function () {
    Route::get('surveys', [SurveyBackendApiController::class, 'index'])->name('surveys');
});

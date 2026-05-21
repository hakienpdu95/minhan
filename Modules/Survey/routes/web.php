<?php

use Illuminate\Support\Facades\Route;
use Modules\Survey\Http\Controllers\Admin\FieldController;
use Modules\Survey\Http\Controllers\Admin\OptionController;
use Modules\Survey\Http\Controllers\Admin\SectionController;
use Modules\Survey\Http\Controllers\Admin\SurveyController;

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

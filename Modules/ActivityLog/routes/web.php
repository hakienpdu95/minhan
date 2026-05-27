<?php

use Modules\ActivityLog\Http\Controllers\ActivityLogApiController;
use Modules\ActivityLog\Http\Controllers\ActivityLogController;
use Modules\ActivityLog\Http\Controllers\AlertRuleController;

Route::prefix('dashboard/activity-logs')
    ->middleware(['web', 'auth', 'can:activitylog.view'])
    ->name('activitylog.')
    ->group(function () {
        Route::get('/',                      [ActivityLogController::class, 'index'])         ->name('index');
        Route::get('/{log}',                 [ActivityLogController::class, 'show'])          ->name('show');
        Route::post('/export',               [ActivityLogController::class, 'export'])        ->name('export');
        Route::get('/export/download/{key}', [ActivityLogController::class, 'downloadExport'])->name('export.download');

        Route::prefix('alert-rules')->name('alert-rules.')->middleware('can:activitylog.manage_alerts')->group(function () {
            Route::get('/',          [AlertRuleController::class, 'index'])  ->name('index');
            Route::post('/',         [AlertRuleController::class, 'store'])  ->name('store');
            Route::put('/{rule}',    [AlertRuleController::class, 'update']) ->name('update');
            Route::delete('/{rule}', [AlertRuleController::class, 'destroy'])->name('destroy');
        });
    });

Route::prefix('backend/api/activity-logs')
    ->middleware(['web', 'auth', 'can:activitylog.view'])
    ->name('backend.api.activitylog.')
    ->group(function () {
        Route::get('/',      [ActivityLogApiController::class, 'index']) ->name('index');
        Route::get('/stats', [ActivityLogApiController::class, 'stats']) ->name('stats');
        Route::get('/meta',  [ActivityLogApiController::class, 'meta'])  ->name('meta');
    });

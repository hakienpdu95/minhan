<?php

use Illuminate\Support\Facades\Route;
use Modules\OrgChart\Http\Controllers\OrgChartController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('orgcharts', OrgChartController::class)->names('orgchart');
});

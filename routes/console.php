<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Modules\Survey\Jobs\PurgeDeletedResponsesJob;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// GDPR: hard-purge soft-deleted survey responses older than 30 days
Schedule::job(new PurgeDeletedResponsesJob())->dailyAt('03:00')->onOneServer();

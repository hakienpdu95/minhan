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

// Workflow: purge old execution logs beyond retain_execution_days
Schedule::call(\Modules\WorkflowAutomation\Actions\PurgeOldExecutionsAction::make())
    ->name('workflow:purge-executions')
    ->dailyAt('02:00')
    ->onOneServer();

// KC: auto-archive expired documents
Schedule::command('kc:expire-items')
    ->name('kc:expire-items')
    ->dailyAt('01:00')
    ->onOneServer();

// SOP: auto-archive SOPs past their expired_date
Schedule::command('sop:archive-expired')
    ->name('sop:archive-expired')
    ->dailyAt('01:30')
    ->onOneServer();

// SOP: warn owners of SOPs expiring within 7 days (every Monday)
Schedule::command('sop:expiry-warning')
    ->name('sop:expiry-warning')
    ->weeklyOn(1, '08:00')
    ->onOneServer();

// JP: auto-close expired job posts
Schedule::command('jp:expire-posts')
    ->name('jp:expire-posts')
    ->dailyAt('00:30')
    ->onOneServer();

// JP: notify owners of job posts expiring in 7/3/1 days
Schedule::command('jp:expiry-warning')
    ->name('jp:expiry-warning')
    ->dailyAt('08:00')
    ->onOneServer();

// Media: cleanup Jodit orphan images older than 24h — chạy mỗi 4h
Schedule::command('media:cleanup-orphans')
    ->name('media:cleanup-orphans')
    ->everyFourHours()
    ->onOneServer();

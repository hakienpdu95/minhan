<?php

namespace Modules\WorkflowAutomation\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Assessment\Events\AssessmentCompleted;
use Modules\WorkflowAutomation\Listeners\FireWorkflowOnAssessmentCompleted;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AssessmentCompleted::class => [
            FireWorkflowOnAssessmentCompleted::class,
        ],
    ];

    protected static $shouldDiscoverEvents = false;

    protected function configureEmailVerification(): void {}
}

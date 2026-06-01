<?php

namespace Modules\Assessment\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class AssessmentServiceProvider extends ModuleServiceProvider
{
    protected string $name      = 'Assessment';
    protected string $nameLower = 'assessment';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    // The `assessment.result_calculated` trigger is now declared in
    // config('workflow_automation.triggers'); AssessmentCompleted implements
    // ProvidesWorkflowContext, so no manual registration is required here.
}

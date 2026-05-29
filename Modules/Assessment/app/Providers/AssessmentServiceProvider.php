<?php

namespace Modules\Assessment\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Modules\Assessment\WorkflowTriggers\AssessmentResultBandTrigger;

class AssessmentServiceProvider extends ModuleServiceProvider
{
    protected string $name      = 'Assessment';
    protected string $nameLower = 'assessment';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        // Đăng ký Workflow trigger
        if (class_exists(\Modules\WorkflowAutomation\Core\TriggerRegistry::class)) {
            $this->app->booted(function () {
                app(\Modules\WorkflowAutomation\Core\TriggerRegistry::class)
                    ->register(app(AssessmentResultBandTrigger::class));
            });
        }
    }
}

<?php

namespace Modules\WorkflowAutomation\Providers;

use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Modules\WorkflowAutomation\Core\ActionRegistry;
use Modules\WorkflowAutomation\Core\ConditionEvaluator;
use Modules\WorkflowAutomation\Core\CooldownGuard;
use Modules\WorkflowAutomation\Core\SubjectRegistry;
use Modules\WorkflowAutomation\Core\TriggerRegistry;
use Modules\WorkflowAutomation\Executors\CallWebhookExecutor;
use Modules\WorkflowAutomation\Executors\SendEmailExecutor;
use Modules\WorkflowAutomation\Executors\SendNotificationExecutor;
use Modules\WorkflowAutomation\Executors\UpdateSubjectExecutor;
use Modules\WorkflowAutomation\Triggers\ManualTrigger;
use Nwidart\Modules\Support\ModuleServiceProvider;

class WorkflowAutomationServiceProvider extends ModuleServiceProvider
{
    protected string $name      = 'WorkflowAutomation';
    protected string $nameLower = 'workflowautomation';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(TriggerRegistry::class);
        $this->app->singleton(ActionRegistry::class);
        $this->app->singleton(SubjectRegistry::class);
        $this->app->singleton(ConditionEvaluator::class);
        $this->app->singleton(CooldownGuard::class, fn ($app) =>
            new CooldownGuard($app->make(CacheRepository::class))
        );
    }

    public function boot(): void
    {
        parent::boot();

        $triggerRegistry = app(TriggerRegistry::class);
        $triggerRegistry->register(new ManualTrigger());

        // Assessment triggers
        if (class_exists(\Modules\Assessment\WorkflowTriggers\AssessmentResultBandTrigger::class)) {
            $triggerRegistry->register(app(\Modules\Assessment\WorkflowTriggers\AssessmentResultBandTrigger::class));
        }

        $actionRegistry = app(ActionRegistry::class);
        $actionRegistry->register(app(SendEmailExecutor::class));
        $actionRegistry->register(app(SendNotificationExecutor::class));
        $actionRegistry->register(app(UpdateSubjectExecutor::class));
        $actionRegistry->register(app(CallWebhookExecutor::class));
    }
}

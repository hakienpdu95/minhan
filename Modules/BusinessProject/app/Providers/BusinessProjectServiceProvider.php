<?php

namespace Modules\BusinessProject\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\BusinessProject\Contracts\DeliverableSignatureProvider;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\ChangeRequest;
use Modules\BusinessProject\Models\Deliverable;
use Modules\BusinessProject\Models\DeliverableTemplate;
use Modules\BusinessProject\Policies\BusinessProjectPolicy;
use Modules\BusinessProject\Policies\ChangeRequestPolicy;
use Modules\BusinessProject\Policies\DeliverablePolicy;
use Modules\BusinessProject\Policies\DeliverableTemplatePolicy;
use Modules\BusinessProject\Signature\InternalRsaSignatureProvider;
use Modules\BusinessProject\Workflow\CreateProjectRetrospectiveExecutor;
use Nwidart\Modules\Support\ModuleServiceProvider;

class BusinessProjectServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'BusinessProject';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'businessproject';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        $this->app->bind(DeliverableSignatureProvider::class, match (config('businessproject.signature.provider', 'internal_rsa')) {
            default => InternalRsaSignatureProvider::class,
        });

        Gate::policy(BusinessProject::class, BusinessProjectPolicy::class);
        Gate::policy(Deliverable::class, DeliverablePolicy::class);
        Gate::policy(ChangeRequest::class, ChangeRequestPolicy::class);
        Gate::policy(DeliverableTemplate::class, DeliverableTemplatePolicy::class);

        // Workflow Engine (Phase 3) — Modules/WorkflowAutomation có thể chưa boot xong lúc
        // BusinessProjectServiceProvider::boot() chạy, nên đăng ký sau khi app booted xong
        // (cùng pattern LeadServiceProvider::boot()).
        if (class_exists(\Modules\WorkflowAutomation\Core\ActionRegistry::class)) {
            $this->app->booted(function () {
                app(\Modules\WorkflowAutomation\Core\ActionRegistry::class)
                    ->register(app(CreateProjectRetrospectiveExecutor::class));
            });
        }
    }
}

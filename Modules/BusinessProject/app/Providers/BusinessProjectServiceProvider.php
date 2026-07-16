<?php

namespace Modules\BusinessProject\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\ChangeRequest;
use Modules\BusinessProject\Models\Deliverable;
use Modules\BusinessProject\Policies\BusinessProjectPolicy;
use Modules\BusinessProject\Policies\ChangeRequestPolicy;
use Modules\BusinessProject\Policies\DeliverablePolicy;
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

        Gate::policy(BusinessProject::class, BusinessProjectPolicy::class);
        Gate::policy(Deliverable::class, DeliverablePolicy::class);
        Gate::policy(ChangeRequest::class, ChangeRequestPolicy::class);
    }
}

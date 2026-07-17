<?php

namespace Modules\BusinessProject\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\BusinessProject\Listeners\SeedBcosWorkflowsOnOrganizationCreated;
use Modules\Organization\Events\OrganizationCreated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        OrganizationCreated::class => [
            SeedBcosWorkflowsOnOrganizationCreated::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}

<?php

namespace Modules\Organization\Providers;

use Illuminate\Routing\Router;
use Modules\Organization\Http\Middleware\SetCurrentOrganization;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Spatie\Permission\PermissionRegistrar;

class OrganizationServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Organization';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'organization';

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register(); // registers RouteServiceProvider
    }

    public function boot(): void
    {
        parent::boot();

        // Enable Spatie Teams feature AFTER all providers have booted.
        // Using $this->app->booted() (Application-level) ensures this runs
        // after PermissionServiceProvider and all other providers.
        $this->app->booted(function (): void {
            config([
                'permission.teams'                               => true,
                'permission.column_names.team_foreign_key'       => 'organization_id',
            ]);

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });

        // Register middleware alias
        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('current_organization', SetCurrentOrganization::class);
    }
}

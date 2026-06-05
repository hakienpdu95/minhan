<?php

namespace Modules\Marketplace\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\JobPosting\Models\JpJobPost;
use Modules\Marketplace\Models\MktListing;
use Modules\Marketplace\Observers\JpJobPostObserver;
use Modules\Marketplace\Policies\MktListingPolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;

class MarketplaceServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Marketplace';
    protected string $nameLower = 'marketplace';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();

        Gate::policy(MktListing::class, MktListingPolicy::class);

        // Register JP observer for marketplace sync
        if (class_exists(JpJobPost::class)) {
            JpJobPost::observe(JpJobPostObserver::class);
        }
    }
}

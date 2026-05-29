<?php

namespace Modules\Lead\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Lead\Events\LeadAssigned;
use Modules\Lead\Events\LeadCreated;
use Modules\Lead\Events\LeadStageChanged;
use Modules\Lead\Events\LeadUpdated;
use Modules\Lead\Events\TagCreated;
use Modules\Lead\Events\TagDeleted;
use Modules\Lead\Events\TagUpdated;
use Modules\Lead\Listeners\FlushTagsCache;
use Modules\Lead\Listeners\LogLeadAssigned;
use Modules\Lead\Listeners\LogLeadCreated;
use Modules\Lead\Listeners\LogLeadStageChanged;
use Modules\Lead\Listeners\LogLeadUpdated;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        LeadCreated::class => [
            LogLeadCreated::class,
        ],
        LeadUpdated::class => [
            LogLeadUpdated::class,
        ],
        LeadStageChanged::class => [
            LogLeadStageChanged::class,
        ],
        LeadAssigned::class => [
            LogLeadAssigned::class,
        ],
        TagCreated::class => [
            FlushTagsCache::class,
        ],
        TagUpdated::class => [
            FlushTagsCache::class,
        ],
        TagDeleted::class => [
            FlushTagsCache::class,
        ],
    ];

    protected static $shouldDiscoverEvents = false;

    protected function configureEmailVerification(): void {}
}

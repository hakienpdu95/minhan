<?php

namespace Modules\Survey\Providers;

use Modules\Survey\Models\Survey;
use Modules\Survey\Observers\SurveyObserver;
use Nwidart\Modules\Support\ModuleServiceProvider;

class SurveyServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Survey';
    protected string $nameLower = 'survey';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        parent::boot();
        Survey::observe(SurveyObserver::class);
    }
}

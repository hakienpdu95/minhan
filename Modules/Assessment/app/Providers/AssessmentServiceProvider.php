<?php

namespace Modules\Assessment\Providers;

use Modules\Assessment\Console\Commands\ExpireCertificationsCommand;
use Nwidart\Modules\Support\ModuleServiceProvider;

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
        $this->commands([ExpireCertificationsCommand::class]);
    }
}

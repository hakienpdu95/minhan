<?php

namespace Modules\Assessment\Providers;

use Modules\Assessment\Console\Commands\AutoSuspendExpiredMembershipsCommand;
use Modules\Assessment\Console\Commands\ExpireCertificationsCommand;
use Modules\Assessment\Console\Commands\FlagInactiveMembersCommand;
use Modules\Assessment\Services\Ocr\Contracts\CccdOcrDriverInterface;
use Modules\Assessment\Services\Ocr\Drivers\FptAiOcrDriver;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AssessmentServiceProvider extends ModuleServiceProvider
{
    protected string $name      = 'Assessment';
    protected string $nameLower = 'assessment';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        // Bind OCR driver — đổi FptAiOcrDriver sang driver khác ở đây nếu muốn thay provider.
        $this->app->bind(CccdOcrDriverInterface::class, FptAiOcrDriver::class);
    }

    public function boot(): void
    {
        parent::boot();
        $this->commands([
            ExpireCertificationsCommand::class,
            AutoSuspendExpiredMembershipsCommand::class,
            FlagInactiveMembersCommand::class,
        ]);
    }
}

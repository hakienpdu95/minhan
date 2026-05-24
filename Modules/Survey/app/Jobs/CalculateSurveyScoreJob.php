<?php

namespace Modules\Survey\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\Survey\Actions\CalculateSurveyScoreAction;

class CalculateSurveyScoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(
        public readonly int  $responseId,
        public readonly bool $force = false,
    ) {
        $this->onQueue('default');
    }

    public function handle(CalculateSurveyScoreAction $action): void
    {
        $action->handle($this->responseId, $this->force);
    }
}

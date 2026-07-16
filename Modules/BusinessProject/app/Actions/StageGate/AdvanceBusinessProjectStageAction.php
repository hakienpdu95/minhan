<?php

namespace Modules\BusinessProject\Actions\StageGate;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Exceptions\GateViolationException;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityHandler;
use Modules\BusinessProject\Queries\StageGate\CheckStageGateEligibilityQuery;

class AdvanceBusinessProjectStageAction
{
    use AsAction;

    public function __construct(
        private readonly CheckStageGateEligibilityHandler $eligibilityHandler,
    ) {}

    public function handle(BusinessProject $businessProject): BusinessProject
    {
        $result = $this->eligibilityHandler->handle(
            new CheckStageGateEligibilityQuery($businessProject)
        );

        if (! $result->canAdvance || $result->nextStage === null) {
            throw new GateViolationException($result);
        }

        $businessProject->update([
            'current_stage' => $result->nextStage,
            'updated_by' => Auth::id(),
        ]);

        return $businessProject;
    }
}

<?php

namespace Modules\BusinessProject\Actions\StageGate;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Enums\BusinessProjectStage;
use Modules\BusinessProject\Events\BusinessProjectClosed;
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

        // Rời khỏi Closing (đã qua Gate R6/R7) CHÍNH LÀ hành động "Đóng dự án" (spec Giai đoạn 6)
        // — đánh dấu status=closed + bắn event, không cần nút "Đóng dự án" riêng.
        $isClosingProject = $businessProject->current_stage === BusinessProjectStage::Closing;

        $businessProject->update([
            'current_stage' => $result->nextStage,
            'status' => $isClosingProject ? 'closed' : $businessProject->status,
            'updated_by' => Auth::id(),
        ]);

        if ($isClosingProject) {
            event(new BusinessProjectClosed($businessProject));
        }

        return $businessProject;
    }
}

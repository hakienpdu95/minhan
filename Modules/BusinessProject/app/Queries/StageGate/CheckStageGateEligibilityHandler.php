<?php

namespace Modules\BusinessProject\Queries\StageGate;

use App\Shared\Contracts\QueryHandlerInterface;
use App\Shared\Contracts\QueryInterface;
use Modules\BusinessProject\Data\StageGateConditionData;
use Modules\BusinessProject\Data\StageGateResultData;
use Modules\BusinessProject\Enums\BusinessProjectStage;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Enums\DeliverableType;

/**
 * Nơi DUY NHẤT quyết định "đủ điều kiện chuyển giai đoạn hay chưa" (Rule R1-R7).
 * Phải có đủ 8 nhánh match() ngay từ Vertical Slice 1 (spec Phần 9 — bypass Diagnosis
 * ở Phase 1 KHÔNG được code cứng bỏ qua state, chỉ được tắt qua điều kiện chưa triển khai
 * ở đây, để Phase 2 chỉ cần thay nội dung 1 case, không đổi cấu trúc).
 */
class CheckStageGateEligibilityHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): StageGateResultData
    {
        /** @var CheckStageGateEligibilityQuery $query */
        $project = $query->businessProject;
        $stage = $project->current_stage instanceof BusinessProjectStage
            ? $project->current_stage
            : BusinessProjectStage::from($project->current_stage);

        $conditions = match ($stage) {
            BusinessProjectStage::Context => $this->contextConditions($project),
            default => $this->notImplementedConditions(),
        };

        $canAdvance = collect($conditions)->every(fn (StageGateConditionData $c) => $c->met);

        return new StageGateResultData(
            stage: $stage->value,
            nextStage: $stage->next()?->value,
            canAdvance: $canAdvance,
            conditions: $conditions,
        );
    }

    /**
     * Rule R1/R2 tại gate Context -> Discovery: phải có Business Context VÀ
     * Deliverable Context Report đã được duyệt (approved) qua Approval Service.
     *
     * @return StageGateConditionData[]
     */
    private function contextConditions($project): array
    {
        $context = $project->context;

        $contextReport = $context?->deliverable_id
            ? $project->deliverables()
                ->where('id', $context->deliverable_id)
                ->where('type', DeliverableType::BusinessContextReport->value)
                ->first()
            : null;

        return [
            new StageGateConditionData(
                label: 'Đã nhập Business Context (Company Profile, Stakeholder, Strategic Goals)',
                met: $context !== null,
            ),
            new StageGateConditionData(
                label: 'Business Context Report đã được phê duyệt',
                met: $contextReport !== null
                    && $contextReport->status?->value === DeliverableStatus::Approved->value,
            ),
        ];
    }

    /**
     * @return StageGateConditionData[]
     */
    private function notImplementedConditions(): array
    {
        return [
            new StageGateConditionData(
                label: 'Workspace chưa triển khai ở Phase này',
                met: false,
            ),
        ];
    }
}

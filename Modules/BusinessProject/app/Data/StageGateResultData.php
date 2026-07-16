<?php

namespace Modules\BusinessProject\Data;

use Spatie\LaravelData\Data;

/**
 * Kết quả của CheckStageGateEligibilityQuery — dùng để (a) quyết định trong
 * AdvanceBusinessProjectStageAction, (b) render trực tiếp ra Gate checklist ở
 * Right Sidebar (Phần 5B của spec) mà không cần tầng nào khác diễn giải lại.
 *
 * @property StageGateConditionData[] $conditions
 */
class StageGateResultData extends Data
{
    public function __construct(
        public readonly string $stage,
        public readonly ?string $nextStage,
        public readonly bool $canAdvance,
        public readonly array $conditions,
    ) {}
}

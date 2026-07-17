<?php

namespace Modules\BusinessProject\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\BusinessProject\Models\BusinessProject;

/**
 * Bắn ở MỌI lần advance stage (khác BusinessProjectClosed — chỉ bắn khi rời Closing). Cho phép
 * Workflow Engine (Phase 3) tự động hoá theo từng stage cụ thể qua trigger
 * `business_project.stage_advanced` (config `workflow_automation.triggers`), không chỉ lúc đóng.
 */
class BusinessProjectStageAdvanced
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly BusinessProject $businessProject,
        public readonly string $stageFrom,
        public readonly string $stageTo,
    ) {}
}

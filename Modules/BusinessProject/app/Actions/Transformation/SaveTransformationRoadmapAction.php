<?php

namespace Modules\BusinessProject\Actions\Transformation;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Actions\Deliverable\UpsertSingletonDeliverableAction;
use Modules\BusinessProject\Data\Requests\StoreTransformationRoadmapData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;

/**
 * Bản tổng quan lộ trình chuyển đổi (versioned qua Deliverable Engine) — các mốc cụ thể theo
 * 4 lớp thời gian nằm ở bảng riêng `milestones` (xem AddMilestoneAction), không nhúng vào đây.
 */
class SaveTransformationRoadmapAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreTransformationRoadmapData $data): Deliverable
    {
        return UpsertSingletonDeliverableAction::run(
            $businessProject,
            DeliverableType::TransformationRoadmap,
            'Transformation Roadmap',
            [
                'overview' => $data->overview,
            ],
            'Cập nhật Transformation Roadmap.',
        );
    }
}

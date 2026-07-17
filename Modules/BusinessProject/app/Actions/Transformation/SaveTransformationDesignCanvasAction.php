<?php

namespace Modules\BusinessProject\Actions\Transformation;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Actions\Deliverable\UpsertSingletonDeliverableAction;
use Modules\BusinessProject\Data\Requests\StoreTransformationDesignCanvasData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;

/**
 * Transformation Design Canvas (Handbook 5.5 ⭐) — dùng để thống nhất với doanh nghiệp trước
 * khi lập Roadmap (spec Giai đoạn 4), KHÔNG có Approval/Confirm (khác Proposal/SOW).
 */
class SaveTransformationDesignCanvasAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreTransformationDesignCanvasData $data): Deliverable
    {
        return UpsertSingletonDeliverableAction::run(
            $businessProject,
            DeliverableType::TransformationDesignCanvas,
            'Transformation Design Canvas',
            [
                'business_goal' => $data->business_goal,
                'priority_problems' => $data->priority_problems,
                'transformation_objectives' => $data->transformation_objectives,
                'key_initiatives' => $data->key_initiatives,
                'quick_wins' => $data->quick_wins,
                'resources' => $data->resources,
                'risks' => $data->risks,
                'success_metrics' => $data->success_metrics,
            ],
            'Cập nhật Transformation Design Canvas.',
            $data->template_id,
        );
    }
}

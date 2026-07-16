<?php

namespace Modules\BusinessProject\Actions\Discovery;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Actions\Deliverable\UpsertSingletonDeliverableAction;
use Modules\BusinessProject\Data\Requests\StoreTpsCanvasData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;

/**
 * TPS Canvas (THUCHOCVN Problem Solving Canvas) — công cụ tổng hợp/đồng thuận của Discovery
 * Workspace (spec Giai đoạn 2 + Handbook 4.5), dùng sau khi đã đủ bối cảnh từ Interview/
 * Observation, KHÔNG phải bước đầu tiên. 3 trường Problem/Goal/Scope đúng mục đích Handbook:
 * "Xác định nhanh bài toán, mục tiêu và phạm vi dự án".
 */
class SaveTpsCanvasAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreTpsCanvasData $data): Deliverable
    {
        return UpsertSingletonDeliverableAction::run(
            $businessProject,
            DeliverableType::TpsCanvas,
            'TPS Canvas',
            [
                'problem' => $data->problem,
                'goal' => $data->goal,
                'scope' => $data->scope,
            ],
            'Cập nhật TPS Canvas.',
        );
    }
}

<?php

namespace Modules\BusinessProject\Actions\Transformation;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Actions\Deliverable\UpsertSingletonDeliverableAction;
use Modules\BusinessProject\Data\Requests\StoreSowData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;

class SaveSowAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreSowData $data): Deliverable
    {
        return UpsertSingletonDeliverableAction::run(
            $businessProject,
            DeliverableType::Sow,
            'Statement of Work (SOW)',
            [
                'scope' => $data->scope,
                'deliverables' => $data->deliverables,
                'responsibilities' => $data->responsibilities,
            ],
            'Cập nhật Statement of Work.',
            $data->template_id,
        );
    }
}

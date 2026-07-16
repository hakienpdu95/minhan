<?php

namespace Modules\BusinessProject\Actions\Transformation;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Actions\Deliverable\UpsertSingletonDeliverableAction;
use Modules\BusinessProject\Data\Requests\StoreProposalData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;

class SaveProposalAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreProposalData $data): Deliverable
    {
        return UpsertSingletonDeliverableAction::run(
            $businessProject,
            DeliverableType::Proposal,
            'Proposal',
            [
                'solution' => $data->solution,
                'collaboration_plan' => $data->collaboration_plan,
            ],
            'Cập nhật Proposal.',
            $data->template_id,
        );
    }
}

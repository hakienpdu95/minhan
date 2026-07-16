<?php

namespace Modules\BusinessProject\Actions\Closing;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Actions\Deliverable\UpsertSingletonDeliverableAction;
use Modules\BusinessProject\Data\Requests\StoreFinalReportData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;

class SaveFinalReportAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreFinalReportData $data): Deliverable
    {
        return UpsertSingletonDeliverableAction::run(
            $businessProject,
            DeliverableType::FinalReport,
            'Final Project Report',
            [
                'summary' => $data->summary,
            ],
            'Cập nhật Final Project Report.',
        );
    }
}

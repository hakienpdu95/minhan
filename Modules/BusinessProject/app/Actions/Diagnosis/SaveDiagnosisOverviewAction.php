<?php

namespace Modules\BusinessProject\Actions\Diagnosis;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Actions\Deliverable\UpsertSingletonDeliverableAction;
use Modules\BusinessProject\Data\Requests\StoreDiagnosisOverviewData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;

/**
 * Diagnosis Report content = {overview, findings: [...]} — SaveOverview chỉ đổi `overview`,
 * PHẢI giữ nguyên `findings` hiện có (đọc version mới nhất trước khi ghi đè qua
 * UpsertSingletonDeliverableAction, tránh mất dữ liệu do action khác chỉ biết field của mình).
 */
class SaveDiagnosisOverviewAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreDiagnosisOverviewData $data): Deliverable
    {
        $existing = $businessProject->deliverables()
            ->where('type', DeliverableType::DiagnosisReport->value)
            ->whereNull('parent_id')
            ->first();

        $findings = $existing?->versions->first()?->content['findings'] ?? [];

        return UpsertSingletonDeliverableAction::run(
            $businessProject,
            DeliverableType::DiagnosisReport,
            'Diagnosis Report',
            [
                'overview' => $data->overview,
                'findings' => $findings,
            ],
            'Cập nhật Diagnosis Report overview.',
            $data->template_id,
        );
    }
}

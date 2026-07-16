<?php

namespace Modules\BusinessProject\Actions\Diagnosis;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Actions\Deliverable\UpsertSingletonDeliverableAction;
use Modules\BusinessProject\Data\Requests\StoreDiagnosisFindingData;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Enums\DiagnosisEffort;
use Modules\BusinessProject\Enums\DiagnosisImpact;
use Modules\BusinessProject\Enums\DiagnosisPriority;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;

/**
 * Thêm 1 dòng vào Diagnosis Matrix (Handbook 4.6). Priority tính tự động từ Impact+Effort
 * (Impact–Effort Matrix, Handbook 4.7) — không nhận priority từ input.
 */
class AddDiagnosisFindingAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, StoreDiagnosisFindingData $data): Deliverable
    {
        $existing = $businessProject->deliverables()
            ->where('type', DeliverableType::DiagnosisReport->value)
            ->whereNull('parent_id')
            ->first();

        $content = $existing?->versions->first()?->content ?? [];
        $findings = $content['findings'] ?? [];

        $impact = DiagnosisImpact::from($data->impact);
        $effort = DiagnosisEffort::from($data->effort);

        $findings[] = [
            'problem' => $data->problem,
            'category' => $data->category,
            'root_cause' => $data->root_cause,
            'impact' => $impact->value,
            'effort' => $effort->value,
            'priority' => DiagnosisPriority::fromImpactAndEffort($impact, $effort)->value,
        ];

        return UpsertSingletonDeliverableAction::run(
            $businessProject,
            DeliverableType::DiagnosisReport,
            'Diagnosis Report',
            [
                'overview' => $content['overview'] ?? null,
                'findings' => $findings,
            ],
            'Thêm finding vào Diagnosis Matrix.',
        );
    }
}

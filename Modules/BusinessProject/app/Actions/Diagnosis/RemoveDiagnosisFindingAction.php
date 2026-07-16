<?php

namespace Modules\BusinessProject\Actions\Diagnosis;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Actions\Deliverable\UpsertSingletonDeliverableAction;
use Modules\BusinessProject\Enums\DeliverableType;
use Modules\BusinessProject\Models\BusinessProject;
use Modules\BusinessProject\Models\Deliverable;
use Symfony\Component\HttpKernel\Exception\HttpException;

class RemoveDiagnosisFindingAction
{
    use AsAction;

    public function handle(BusinessProject $businessProject, int $index): Deliverable
    {
        $existing = $businessProject->deliverables()
            ->where('type', DeliverableType::DiagnosisReport->value)
            ->whereNull('parent_id')
            ->first();

        $content = $existing?->versions->first()?->content ?? [];
        $findings = $content['findings'] ?? [];

        if (! array_key_exists($index, $findings)) {
            throw new HttpException(404, 'Không tìm thấy finding cần xóa.');
        }

        unset($findings[$index]);

        return UpsertSingletonDeliverableAction::run(
            $businessProject,
            DeliverableType::DiagnosisReport,
            'Diagnosis Report',
            [
                'overview' => $content['overview'] ?? null,
                'findings' => array_values($findings),
            ],
            'Xóa finding khỏi Diagnosis Matrix.',
        );
    }
}

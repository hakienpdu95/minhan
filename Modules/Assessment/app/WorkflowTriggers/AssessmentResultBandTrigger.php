<?php

namespace Modules\Assessment\WorkflowTriggers;

use Modules\WorkflowAutomation\Contracts\TriggerSource;
use Modules\WorkflowAutomation\Data\TriggerPayload;

class AssessmentResultBandTrigger implements TriggerSource
{
    public function type(): string   { return 'assessment.result_calculated'; }
    public function label(): string  { return 'Kết quả Assessment được tính điểm'; }
    public function module(): string { return 'Assessment'; }

    public function availableFields(): array
    {
        return [
            ['key' => 'extra.assessment_code', 'label' => 'Assessment Code', 'type' => 'string'],
            ['key' => 'extra.band_code',        'label' => 'Band Code',       'type' => 'string'],
            ['key' => 'extra.overall_score',    'label' => 'Overall Score',   'type' => 'float'],
            ['key' => 'extra.subject_type',     'label' => 'Subject Type',    'type' => 'string'],
            ['key' => 'extra.subject_id',       'label' => 'Subject ID',      'type' => 'integer'],
        ];
    }

    public function configFields(): array
    {
        return [
            ['key' => 'band_code', 'label' => 'Band Code cần match', 'type' => 'text',
             'hint' => 'Để trống = match tất cả band. VD: advanced'],
            ['key' => 'assessment_code', 'label' => 'Assessment Code', 'type' => 'text',
             'hint' => 'Để trống = match tất cả assessment.'],
        ];
    }

    public function matches(TriggerPayload $payload, array $parsedConfig): bool
    {
        if ($payload->triggerType !== $this->type()) {
            return false;
        }

        $requiredBand = $parsedConfig['band_code'] ?? null;
        if ($requiredBand && ($payload->extra['band_code'] ?? null) !== $requiredBand) {
            return false;
        }

        $requiredCode = $parsedConfig['assessment_code'] ?? null;
        if ($requiredCode && ($payload->extra['assessment_code'] ?? null) !== $requiredCode) {
            return false;
        }

        return true;
    }
}

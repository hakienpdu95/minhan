<?php

namespace Modules\Survey\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Data\FieldFormData;
use Modules\Survey\Enums\SurveyStatus;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyField;
use Modules\Survey\Support\GuardsSurveyIntegrity;

class UpdateFieldAction
{
    use AsAction;
    use GuardsSurveyIntegrity;

    public function handle(Survey $survey, SurveyField $field, FieldFormData $data): SurveyField
    {
        $this->guardLockedSurvey($survey);

        // field_key: bất biến vĩnh viễn — không bao giờ cập nhật
        $payload = [
            'label'           => $data->label,
            'is_required'     => $data->is_required,
            'placeholder'     => $data->placeholder,
            'rule_min'        => $data->rule_min,
            'rule_max'        => $data->rule_max,
            'rule_max_select' => $data->rule_max_select,
        ];

        // field_type bất biến khi survey đã active
        if ($survey->status !== SurveyStatus::Active) {
            $payload['field_type'] = $data->field_type;
            $payload['value_kind'] = $data->field_type->valueKind();
        }

        $field->update($payload);

        activity()->performedOn($field)
            ->withProperties(['field_key' => $field->field_key])
            ->log('field.updated');

        return $field->fresh()->load('options');
    }
}

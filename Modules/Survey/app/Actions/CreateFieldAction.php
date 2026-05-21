<?php

namespace Modules\Survey\Actions;

use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Data\FieldFormData;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyField;

class CreateFieldAction
{
    use AsAction;

    public function handle(Survey $survey, FieldFormData $data): SurveyField
    {
        // field_key phải unique trong cùng survey
        $exists = SurveyField::forSurvey($survey->id)
            ->where('field_key', $data->field_key)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'field_key' => "field_key '{$data->field_key}' đã tồn tại trong survey này.",
            ]);
        }

        $maxOrder = SurveyField::forSurvey($survey->id)
            ->when($data->section_id, fn ($q) => $q->where('section_id', $data->section_id))
            ->max('sort_order') ?? 0;

        $field = SurveyField::create([
            'survey_id'       => $survey->id,
            'section_id'      => $data->section_id,
            'parent_field_id' => $data->parent_field_id,
            'field_key'       => $data->field_key,
            'label'           => $data->label,
            'field_type'      => $data->field_type,
            'value_kind'      => $data->field_type->valueKind(),
            'is_required'     => $data->is_required,
            'is_active'       => true,
            'sort_order'      => $maxOrder + 1,
            'rule_min'        => $data->rule_min,
            'rule_max'        => $data->rule_max,
            'rule_max_select' => $data->rule_max_select,
            'placeholder'     => $data->placeholder,
        ]);

        activity()->performedOn($field)
            ->withProperties(['survey_id' => $survey->id, 'field_key' => $field->field_key])
            ->log('field.created');

        return $field->load('options');
    }
}

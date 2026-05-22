<?php

namespace Modules\Survey\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyField;

class DeactivateFieldAction
{
    use AsAction;

    public function handle(SurveyField $field): SurveyField
    {
        $field->is_active = !$field->is_active;
        $field->save();

        $event = $field->is_active ? 'field.activated' : 'field.deactivated';

        activity()->performedOn($field)
            ->withProperties(['field_key' => $field->field_key, 'is_active' => $field->is_active])
            ->log($event);

        $slug = Survey::where('id', $field->survey_id)->value('slug');
        if ($slug) {
            BuildSurveySchemaAction::purgeCache($slug);
        }

        return $field;
    }
}

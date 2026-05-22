<?php

namespace Modules\Survey\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Actions\BuildSurveySchemaAction;
use Modules\Survey\Data\SectionFormData;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveySection;

class UpdateSectionAction
{
    use AsAction;

    public function handle(Survey $survey, SurveySection $section, SectionFormData $data): SurveySection
    {
        $section->update([
            'title' => $data->title,
            'icon'  => $data->icon,
        ]);

        activity()->performedOn($section)
            ->withProperties(['title' => $data->title])
            ->log('section.updated');

        BuildSurveySchemaAction::purgeCache($survey->slug);

        return $section->fresh();
    }
}

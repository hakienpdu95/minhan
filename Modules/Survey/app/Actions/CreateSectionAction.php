<?php

namespace Modules\Survey\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Actions\BuildSurveySchemaAction;
use Modules\Survey\Data\SectionFormData;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveySection;

class CreateSectionAction
{
    use AsAction;

    public function handle(Survey $survey, SectionFormData $data): SurveySection
    {
        $maxOrder = SurveySection::forSurvey($survey->id)->max('sort_order') ?? 0;

        $section = SurveySection::create([
            'survey_id'  => $survey->id,
            'title'      => $data->title,
            'icon'       => $data->icon,
            'sort_order' => $maxOrder + 1,
        ]);

        activity()->performedOn($section)
            ->withProperties(['survey_id' => $survey->id, 'title' => $data->title])
            ->log('section.created');

        BuildSurveySchemaAction::purgeCache($survey->slug);

        return $section;
    }
}

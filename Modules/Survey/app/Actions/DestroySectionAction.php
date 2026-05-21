<?php

namespace Modules\Survey\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveySection;
use Modules\Survey\Support\GuardsSurveyIntegrity;

class DestroySectionAction
{
    use AsAction;
    use GuardsSurveyIntegrity;

    public function handle(Survey $survey, SurveySection $section): void
    {
        $this->guardLockedSurvey($survey);

        $title = $section->title;
        $section->delete();

        activity()
            ->withProperties(['survey_id' => $survey->id, 'title' => $title])
            ->log('section.deleted');
    }
}

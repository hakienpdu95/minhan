<?php

namespace Modules\Survey\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Data\SurveyFormData;
use Modules\Survey\Models\Survey;

class UpdateSurveyAction
{
    use AsAction;

    public function handle(Survey $survey, SurveyFormData $data): Survey
    {
        // Slug do hệ thống sinh ra khi tạo, không bao giờ thay đổi
        $payload = ['title' => $data->title, 'version' => $data->version ?? $survey->version];

        $survey->update($payload);

        activity()
            ->performedOn($survey)
            ->withProperties(['title' => $survey->title])
            ->log('survey.updated');

        return $survey->fresh();
    }
}

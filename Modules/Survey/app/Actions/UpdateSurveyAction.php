<?php

namespace Modules\Survey\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Data\SurveyFormData;
use Modules\ActivityLog\Core\ActivityLogger;
use Modules\Survey\Models\Survey;

class UpdateSurveyAction
{
    use AsAction;

    public function handle(Survey $survey, SurveyFormData $data): Survey
    {
        // Slug do hệ thống sinh ra khi tạo, không bao giờ thay đổi
        $payload = [
            'title'       => $data->title,
            'description' => $data->description,
            'version'     => $data->version ?? $survey->version,
        ];

        $survey->update($payload);

        ActivityLogger::info('Survey', 'survey_updated', $survey, ['title' => $survey->title]);

        return $survey->fresh();
    }
}

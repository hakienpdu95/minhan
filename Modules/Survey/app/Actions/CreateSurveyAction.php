<?php

namespace Modules\Survey\Actions;

use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Data\SurveyFormData;
use Modules\Survey\Enums\SurveyStatus;
use Modules\ActivityLog\Core\ActivityLogger;
use Modules\Survey\Models\Survey;

class CreateSurveyAction
{
    use AsAction;

    public function handle(SurveyFormData $data): Survey
    {
        $slug = $this->uniqueSlug($data->title);

        $survey = Survey::create([
            'title'   => $data->title,
            'slug'    => $slug,
            'status'  => SurveyStatus::Draft,
            'version' => $data->version ?? 1,
        ]);

        ActivityLogger::info('Survey', 'survey_created', $survey, ['title' => $survey->title, 'slug' => $survey->slug]);

        return $survey;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);

        // Append 8-char hex từ UUID để đảm bảo unique mà không cần loop số thứ tự
        do {
            $hash = substr(str_replace('-', '', Str::uuid()->toString()), 0, 8);
            $slug = "{$base}-{$hash}";
        } while (Survey::where('slug', $slug)->exists());

        return $slug;
    }
}

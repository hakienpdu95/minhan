<?php

namespace Modules\Survey\Actions;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Data\SurveyFormData;
use Modules\Survey\Enums\SurveyStatus;
use Modules\Survey\Models\Survey;

class UpdateSurveyAction
{
    use AsAction;

    public function handle(Survey $survey, SurveyFormData $data): Survey
    {
        $payload = ['title' => $data->title, 'version' => $data->version ?? $survey->version];

        // Slug bị khóa sau khi survey active
        if ($survey->status !== SurveyStatus::Active && $data->slug) {
            $newSlug = Str::slug($data->slug);

            // Kiểm tra unique trừ chính nó
            if (Survey::where('slug', $newSlug)->where('id', '!=', $survey->id)->exists()) {
                throw ValidationException::withMessages([
                    'slug' => 'Slug này đã được sử dụng bởi survey khác.',
                ]);
            }

            $payload['slug'] = $newSlug;
        }

        $survey->update($payload);

        activity()
            ->performedOn($survey)
            ->withProperties(['title' => $survey->title])
            ->log('survey.updated');

        return $survey->fresh();
    }
}

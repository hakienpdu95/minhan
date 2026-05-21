<?php

namespace Modules\Survey\Actions;

use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Data\SurveyFormData;
use Modules\Survey\Enums\SurveyStatus;
use Modules\Survey\Models\Survey;

class CreateSurveyAction
{
    use AsAction;

    public function handle(SurveyFormData $data): Survey
    {
        $slug = $data->slug
            ? Str::slug($data->slug)
            : $this->uniqueSlug($data->title);

        $survey = Survey::create([
            'title'   => $data->title,
            'slug'    => $slug,
            'status'  => SurveyStatus::Draft,
            'version' => $data->version ?? 1,
        ]);

        activity()
            ->performedOn($survey)
            ->withProperties(['title' => $survey->title, 'slug' => $survey->slug])
            ->log('survey.created');

        return $survey;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i    = 2;

        while (Survey::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}

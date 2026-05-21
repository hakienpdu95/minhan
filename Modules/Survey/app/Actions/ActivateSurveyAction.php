<?php

namespace Modules\Survey\Actions;

use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Enums\SurveyStatus;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyField;

class ActivateSurveyAction
{
    use AsAction;

    public function handle(Survey $survey): Survey
    {
        if ($survey->status === SurveyStatus::Active) {
            throw ValidationException::withMessages([
                'status' => 'Survey này đã ở trạng thái active.',
            ]);
        }

        $activeFieldCount = SurveyField::forSurvey($survey->id)->active()->count();

        if ($activeFieldCount === 0) {
            throw ValidationException::withMessages([
                'fields' => 'Survey phải có ít nhất 1 field đang active trước khi kích hoạt.',
            ]);
        }

        $survey->update(['status' => SurveyStatus::Active]);

        activity()
            ->performedOn($survey)
            ->withProperties(['slug' => $survey->slug])
            ->log('survey.activated');

        return $survey->fresh();
    }
}

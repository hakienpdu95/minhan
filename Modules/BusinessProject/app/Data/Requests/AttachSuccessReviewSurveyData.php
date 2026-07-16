<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class AttachSuccessReviewSurveyData extends Data
{
    public function __construct(
        public readonly int $survey_response_id,
    ) {}

    public static function rules(): array
    {
        return [
            'survey_response_id' => ['required', 'integer', 'exists:survey_responses,id'],
        ];
    }
}

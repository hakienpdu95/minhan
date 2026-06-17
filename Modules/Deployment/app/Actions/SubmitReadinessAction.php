<?php

namespace Modules\Deployment\Actions;

use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Deployment\Models\DeploymentTarget;
use Modules\Deployment\Services\ReadinessScoreService;
use Modules\Survey\Models\SurveyAnswer;
use Modules\Survey\Models\SurveyField;
use Modules\Survey\Models\SurveyResponse;

class SubmitReadinessAction
{
    use AsAction;

    /**
     * @param  array<int, int>  $answers  [field_id => rating_value (1-5)]
     */
    public function handle(DeploymentTarget $target, array $answers): array
    {
        if (! $target->readiness_response_id) {
            (new CloneSurveyAction)->handle($target);
            $target->refresh();
        }

        $response  = SurveyResponse::findOrFail($target->readiness_response_id);
        $surveyId  = $response->survey_id;

        // Validate fields belong to this survey
        $validFieldIds = SurveyField::where('survey_id', $surveyId)
            ->pluck('id')
            ->flip();

        return DB::transaction(function () use ($response, $target, $answers, $validFieldIds) {
            // Delete previous answers (re-submit allowed)
            SurveyAnswer::where('response_id', $response->id)->delete();

            foreach ($answers as $fieldId => $rating) {
                if (! $validFieldIds->has($fieldId)) continue;
                $rating = max(1, min(5, (int) $rating));

                SurveyAnswer::create([
                    'response_id'  => $response->id,
                    'field_id'     => $fieldId,
                    'value_number' => $rating,
                ]);
            }

            // Mark response as complete
            $response->update([
                'status'       => 1, // complete
                'submitted_at' => now(),
            ]);

            // Compute readiness score
            $result = (new ReadinessScoreService)->compute($response->fresh());

            // Persist score on target
            $target->update(['readiness_score' => $result['score']]);

            return $result;
        });
    }
}

<?php

namespace Modules\Survey\Actions;

use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Models\SurveyResponse;
use Modules\Survey\Scoring\ScoringEngineService;
use Modules\Survey\Services\WebhookDispatcher;

class CalculateSurveyScoreAction
{
    use AsAction;

    public function __construct(
        private readonly ScoringEngineService $engine,
        private readonly WebhookDispatcher   $webhooks,
    ) {}

    public function handle(int $responseId, bool $force = false): void
    {
        $response = SurveyResponse::with('survey')->find($responseId);

        if ($response === null) {
            Log::warning('scoring.action.response_not_found', ['response_id' => $responseId]);
            return;
        }

        $assessmentCode = $response->survey?->assessment_code;

        if ($assessmentCode === null) {
            // Survey này không có scoring — bỏ qua, bình thường
            return;
        }

        try {
            $result = $this->engine->calculate($assessmentCode, $responseId, $force);
        } catch (\Throwable $e) {
            Log::error('scoring.action.failed', [
                'response_id'     => $responseId,
                'assessment_code' => $assessmentCode,
                'error'           => $e->getMessage(),
            ]);
            throw $e;
        }

        // Dispatch webhook after successful scoring
        $this->webhooks->dispatch($response->survey_id, 'result.calculated', [
            'survey_id'      => $response->survey_id,
            'response_id'    => $responseId,
            'respondent_ref' => $response->respondent_ref,
            'overall_score'  => $result->overallScore !== null ? round($result->overallScore, 2) : null,
            'band_code'      => $result->classification->bandCode ?? null,
            'calculated_at'  => now()->toISOString(),
        ]);
    }
}

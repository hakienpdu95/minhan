<?php

namespace Modules\Survey\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Survey\Actions\CalculateSurveyScoreAction;
use Modules\Survey\Models\AssessmentDomain;
use Modules\Survey\Models\MaturityLevel;
use Modules\Survey\Models\RecommendationRule;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyResponse;
use Modules\Survey\Models\SurveyResult;

class SurveyResultController extends Controller
{
    // ── T6.2: Admin xem chi tiết một result ──────────────────────────────────

    public function show(Survey $survey, SurveyResponse $response): \Illuminate\View\View
    {
        $this->authorize('survey.view_responses');
        $this->checkOwnership($response, $survey);

        $result = SurveyResult::forResponse($response->id)
            ->with([
                'domainScores',
                'signalFlags',
                'painPoints',
                'recommendations',
                'roadmapPhases.phase.milestones',
            ])
            ->first();

        // Enrich recommendations với label từ config
        $recLabels = $result
            ? RecommendationRule::where('assessment_code', $result->assessment_code)
                ->pluck('label', 'recommendation_code')
            : collect();

        // Domain labels từ config
        $domainLabels = $survey->assessment_code
            ? AssessmentDomain::forAssessment($survey->assessment_code)
                ->pluck('label', 'domain_code')
            : collect();

        return view('survey::results.show', compact(
            'survey', 'response', 'result', 'recLabels', 'domainLabels'
        ));
    }

    // ── T6.3: Admin xem tổng hợp scoring toàn bộ responses ───────────────────

    public function summary(Survey $survey): \Illuminate\View\View
    {
        $this->authorize('survey.view_responses');

        if (!$survey->hasScoring()) {
            abort(404, 'Survey này không có cấu hình chấm điểm.');
        }

        $assessmentCode = $survey->assessment_code;

        // Phân bố maturity levels
        $maturityDistribution = SurveyResult::whereHas(
            'response', fn ($q) => $q->where('survey_id', $survey->id)
        )
            ->selectRaw('maturity_level, COUNT(*) as count')
            ->groupBy('maturity_level')
            ->pluck('count', 'maturity_level');

        // Avg domain scores
        $avgDomainScores = \Modules\Survey\Models\ResultDomainScore::whereHas(
            'result.response', fn ($q) => $q->where('survey_id', $survey->id)
        )
            ->selectRaw('domain_code, ROUND(AVG(normalized_score), 2) as avg_score')
            ->groupBy('domain_code')
            ->pluck('avg_score', 'domain_code');

        // Overall avg
        $avgOverall = SurveyResult::whereHas(
            'response', fn ($q) => $q->where('survey_id', $survey->id)
        )->avg('overall_score');

        // Total scored
        $totalScored = SurveyResult::whereHas(
            'response', fn ($q) => $q->where('survey_id', $survey->id)
        )->count();

        // Maturity level labels
        $maturityLevels = MaturityLevel::forAssessment($assessmentCode)->ordered()->get();

        // Domain labels
        $domains = AssessmentDomain::forAssessment($assessmentCode)->ordered()->get();

        return view('survey::results.summary', compact(
            'survey',
            'maturityDistribution',
            'avgDomainScores',
            'avgOverall',
            'totalScored',
            'maturityLevels',
            'domains',
        ));
    }

    // ── T6.4: Admin force recalculate một result ──────────────────────────────

    public function recalculate(
        Survey               $survey,
        SurveyResponse       $response,
        CalculateSurveyScoreAction $action,
    ): JsonResponse {
        $this->authorize('survey.update');
        $this->checkOwnership($response, $survey);

        if (!$survey->hasScoring()) {
            return response()->json(['success' => false, 'message' => 'Survey không có cấu hình chấm điểm.'], 422);
        }

        $action->handle($response->id, force: true);

        return response()->json(['success' => true, 'message' => 'Đã tính lại điểm thành công.']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function checkOwnership(SurveyResponse $response, Survey $survey): void
    {
        if ($response->survey_id !== $survey->id) {
            abort(404);
        }
    }
}

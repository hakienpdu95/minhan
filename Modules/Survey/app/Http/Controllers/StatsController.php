<?php

namespace Modules\Survey\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Survey\Models\AssessmentDomain;
use Modules\Survey\Models\MaturityLevel;
use Modules\Survey\Models\ResultDomainScore;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyResult;
use Modules\Survey\Services\SurveyStatsService;

class StatsController extends Controller
{
    public function index(Survey $survey, SurveyStatsService $service)
    {
        $this->authorize('survey.view_responses');

        $stats = $service->forSurvey($survey);
        $byDay = $service->totalByDay($survey, 30);

        $scoringData = null;
        if ($survey->hasScoring()) {
            $code    = $survey->assessment_code;
            $sid     = $survey->id;
            $results = SurveyResult::whereHas('response', fn ($r) => $r->where('survey_id', $sid));

            $scoringData = [
                'total_scored'          => (clone $results)->count(),
                'avg_overall'           => (clone $results)->avg('overall_score'),
                'maturity_distribution' => (clone $results)
                    ->selectRaw('maturity_level, COUNT(*) as count')
                    ->groupBy('maturity_level')
                    ->pluck('count', 'maturity_level'),
                'avg_domain_scores'     => ResultDomainScore::whereHas(
                    'result.response', fn ($r) => $r->where('survey_id', $sid)
                )
                    ->selectRaw('domain_code, ROUND(AVG(normalized_score), 2) as avg_score')
                    ->groupBy('domain_code')
                    ->pluck('avg_score', 'domain_code'),
                'maturity_levels'       => MaturityLevel::forAssessment($code)->ordered()->get(),
                'domains'               => AssessmentDomain::forAssessment($code)->ordered()->get(),
            ];
        }

        return view('survey::stats.index', compact('survey', 'stats', 'byDay', 'scoringData'));
    }
}

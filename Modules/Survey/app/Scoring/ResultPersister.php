<?php

namespace Modules\Survey\Scoring;

use Illuminate\Support\Facades\DB;
use Modules\Survey\Models\ResultClassification;
use Modules\Survey\Models\ResultDomainScore;
use Modules\Survey\Models\ResultPainPoint;
use Modules\Survey\Models\ResultQuestionScore;
use Modules\Survey\Models\ResultRecommendation;
use Modules\Survey\Models\ResultRoadmapPhase;
use Modules\Survey\Models\ResultSignalFlag;
use Modules\Survey\Models\RoadmapPhase;
use Modules\Survey\Models\ResultJobPosition;
use Modules\Survey\Models\ScoringFeedback;
use Modules\Survey\Models\SurveyResult;

class ResultPersister
{
    public function persist(int $responseId, ScoringResult $result): SurveyResult
    {
        return DB::transaction(function () use ($responseId, $result): SurveyResult {
            // Xóa result cũ nếu có (force recalculate) — cascade sẽ xóa children
            SurveyResult::forResponse($responseId)->delete();

            $classification = $result->classification;

            $surveyResult = SurveyResult::create([
                'response_id'     => $responseId,
                'overall_score'   => $result->overallScore,
                'maturity_level'  => $classification->bandCode ?? $classification->personaCode,
                'assessment_code' => $result->assessmentCode,
                'weight_version'  => $result->weightVersion,
                'calculated_at'   => now(),
            ]);

            $resultId = $surveyResult->id;

            // Domain scores
            $domainRows = [];
            foreach ($result->domainScores as $score) {
                $domainRows[] = [
                    'result_id'        => $resultId,
                    'domain_code'      => $score->domainCode,
                    'raw_score'        => $score->rawScore,
                    'normalized_score' => round($score->normalizedScore, 2),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            if (!empty($domainRows)) {
                ResultDomainScore::insert($domainRows);
            }

            // Signal flags
            $flagRows = [];
            foreach ($result->signalFlags as $code => $value) {
                $flagRows[] = [
                    'result_id'  => $resultId,
                    'flag_code'  => $code,
                    'flag_value' => $value ? 1 : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($flagRows)) {
                ResultSignalFlag::insert($flagRows);
            }

            // Pain points
            $painRows = [];
            foreach ($result->painPoints as $code) {
                $painRows[] = [
                    'result_id'       => $resultId,
                    'pain_point_code' => $code,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];
            }
            if (!empty($painRows)) {
                ResultPainPoint::insert($painRows);
            }

            // Recommendations
            $recRows = [];
            foreach ($result->recommendations as $rec) {
                $recRows[] = [
                    'result_id'           => $resultId,
                    'recommendation_code' => $rec->code,
                    'priority'            => $rec->priority,
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ];
            }
            if (!empty($recRows)) {
                ResultRecommendation::insert($recRows);
            }

            // Per-question scores (Tầng 1 output)
            $qScoreRows = [];
            foreach ($result->questionScores as $qs) {
                $qScoreRows[] = [
                    'result_id'        => $resultId,
                    'question_code'    => $qs['question_code'],
                    'feature_code'     => $qs['feature_code'],
                    'raw_score'        => $qs['raw'],
                    'final_score'      => $qs['final'],
                    'selected_options' => $qs['selected'] ?: null,
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ];
            }
            if (!empty($qScoreRows)) {
                ResultQuestionScore::insert($qScoreRows);
            }

            // Classification (Tầng 3 output)
            ResultClassification::create([
                'result_id'           => $resultId,
                'classification_type' => $classification->classificationType,
                'band_code'           => $classification->bandCode,
                'passed'              => $classification->passed,
                'persona_code'        => $classification->personaCode,
                'match_score'         => $classification->matchScore,
            ]);

            // Roadmap phases (lookup phase_id từ phase_code + assessment_code)
            foreach ($result->roadmap as $i => $phaseResult) {
                $phase = RoadmapPhase::where('assessment_code', $result->assessmentCode)
                    ->where('phase_code', $phaseResult->phaseCode)
                    ->first();

                if ($phase) {
                    ResultRoadmapPhase::create([
                        'result_id'  => $resultId,
                        'phase_id'   => $phase->id,
                        'sort_order' => $i,
                    ]);
                }
            }

            // Job positions (Module 150C)
            $jpRows = [];
            foreach ($result->jobPositions as $jp) {
                $jpRows[] = [
                    'result_id'     => $resultId,
                    'position_code' => $jp->positionCode,
                    'match_score'   => round($jp->matchScore, 2),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
            if (!empty($jpRows)) {
                ResultJobPosition::insert($jpRows);
            }

            // Module 170A — seed scoring_feedback so tuning loop has data to learn from
            ScoringFeedback::create([
                'result_id'       => $resultId,
                'assessment_code' => $result->assessmentCode,
                'predicted_band'  => $classification->bandCode ?? $classification->personaCode,
                'predicted_score' => $result->overallScore,
                'actual_band'     => null,
                'actual_score'    => null,
                'feedback_source' => 'system',
                'is_processed'    => false,
            ]);

            return $surveyResult;
        });
    }
}

<?php

namespace Modules\Survey\Scoring;

use Illuminate\Support\Facades\Log;
use Modules\Survey\Models\SurveyResponse;
use Modules\Survey\Models\SurveyResult;

/**
 * CompetencyScoringOrchestrator — điều phối toàn bộ pipeline chấm điểm.
 *
 * Pipeline:
 *   110 Submit → 120 FeatureExtractor (Tầng 1)
 *   → WeightRepository → 130 AggregationFactory (Tầng 2)
 *   → 140 ClassificationFactory (Tầng 3)
 *   → 150 Pain points + Recommendations + Roadmap
 *   → 160 ResultPersister (transaction)
 */
class ScoringEngineService
{
    public function __construct(
        private readonly ScoringConfigLoader  $configLoader,
        private readonly AnswerReader         $answerReader,
        private readonly FeatureExtractor     $featureExtractor,
        private readonly WeightRepository     $weightRepository,
        private readonly AggregationFactory   $aggregationFactory,
        private readonly ClassificationFactory $classificationFactory,
        private readonly PainPointDetector    $painPointDetector,
        private readonly RecommendationEngine $recommendationEngine,
        private readonly RoadmapLoader        $roadmapLoader,
        private readonly JobPositionMatcher   $jobPositionMatcher,
        private readonly ResultPersister      $persister,
    ) {}

    /**
     * @throws \Modules\Survey\Exceptions\InvalidScoringConfigException
     */
    public function calculate(string $assessmentCode, int $responseId, bool $force = false): ScoringResult
    {
        // Idempotency: nếu đã có result và không force → return existing
        if (!$force) {
            $existing = SurveyResult::forResponse($responseId)->first();
            if ($existing !== null) {
                Log::info('scoring.skipped.already_exists', ['response_id' => $responseId]);
                return $this->buildResultFromModel($existing);
            }
        }

        $response = SurveyResponse::findOrFail($responseId);

        // 1. Load + validate config từ DB
        $config = $this->configLoader->load($assessmentCode);

        // Kiểm tra has_scoring flag
        if (!$config->hasScoring()) {
            Log::info('scoring.skipped.no_scoring', ['assessment_code' => $assessmentCode]);
            return $this->buildNullResult($assessmentCode);
        }

        // 2. Load answers từ survey_answers (Module 110)
        $answers      = $this->answerReader->read($responseId, $response->survey_id);
        $behaviorData = $this->answerReader->readBehavior($responseId);

        // 3. Tầng 1 — Feature Extraction (Module 120)
        [
            'rawScores'      => $rawScores,
            'signalFlags'    => $signalFlags,
            'questionScores' => $questionScores,
        ] = $this->featureExtractor->extract($config, $answers, $behaviorData);

        // 4. Load weights (Module 130)
        ['weights' => $weights, 'version' => $weightVersion] =
            $this->weightRepository->loadActive($assessmentCode, $config);

        // 5. Tầng 2 — Aggregation (Module 130)
        $aggregated = $this->aggregationFactory
            ->make($config->aggregationModel())
            ->aggregate($config, $rawScores, $weights);

        // 6. Tầng 3 — Classification (Module 140)
        $classification = $this->classificationFactory
            ->make($config->classificationType())
            ->classify($config, $aggregated, $signalFlags);

        // 7. Pain points (Module 150)
        $painPoints = $this->painPointDetector->detect($config, $signalFlags);

        // 8. Recommendations (Module 150)
        $recommendations = $this->recommendationEngine->evaluate($config, $aggregated->domainScores);

        // 9. Roadmap (Module 150)
        $roadmap = $this->roadmapLoader->load($assessmentCode, $classification);

        // 9.5 — Job Position Matching (Module 150C)
        $jobPositions = $this->jobPositionMatcher->match($config, $aggregated->domainScores, $aggregated->overallScore);

        $result = new ScoringResult(
            overallScore:    $aggregated->overallScore,
            assessmentCode:  $assessmentCode,
            classification:  $classification,
            domainScores:    $aggregated->domainScores,
            sectionScores:   $aggregated->sectionScores,
            signalFlags:     $signalFlags,
            painPoints:      $painPoints,
            recommendations: $recommendations,
            roadmap:         $roadmap,
            weightVersion:   $weightVersion,
            questionScores:  $questionScores,
            jobPositions:    $jobPositions,
        );

        // 10. Persist (Module 160) — toàn bộ trong một transaction
        $this->persister->persist($responseId, $result);

        Log::info('scoring.calculated', [
            'response_id'     => $responseId,
            'assessment_code' => $assessmentCode,
            'overall_score'   => $result->overallScore !== null ? round($result->overallScore, 2) : null,
            'classification'  => $classification->bandCode ?? $classification->personaCode,
        ]);

        return $result;
    }

    private function buildNullResult(string $assessmentCode): ScoringResult
    {
        return new ScoringResult(
            overallScore:    null,
            assessmentCode:  $assessmentCode,
            classification:  ClassificationResult::none(),
            domainScores:    [],
            sectionScores:   [],
            signalFlags:     [],
            painPoints:      [],
            recommendations: [],
            roadmap:         [],
            weightVersion:   1,
        );
    }

    private function buildResultFromModel(SurveyResult $model): ScoringResult
    {
        $model->load([
            'domainScores',
            'signalFlags',
            'painPoints',
            'recommendations',
            'roadmapPhases.phase.milestones',
            'classification',
            'questionScores',
        ]);

        $domainScores = [];
        foreach ($model->domainScores as $ds) {
            $domainScores[$ds->domain_code] = new DomainScoreResult(
                domainCode:      $ds->domain_code,
                rawScore:        $ds->raw_score,
                normalizedScore: $ds->normalized_score,
            );
        }

        $signalFlags = [];
        foreach ($model->signalFlags as $flag) {
            $signalFlags[$flag->flag_code] = (bool) $flag->flag_value;
        }

        $painPoints = $model->painPoints->pluck('pain_point_code')->all();

        $recommendations = $model->recommendations->map(fn ($r) => new RecommendationResult(
            code:        $r->recommendation_code,
            label:       '',
            description: null,
            priority:    $r->priority,
        ))->all();

        $roadmap = $model->roadmapPhases->map(fn ($rp) => new RoadmapPhaseResult(
            phaseCode:     $rp->phase->phase_code ?? '',
            title:         $rp->phase->title ?? '',
            description:   $rp->phase?->description,
            durationWeeks: $rp->phase?->duration_weeks,
            milestones:    $rp->phase?->milestones->pluck('title')->all() ?? [],
        ))->all();

        $classification = $model->classification
            ? new ClassificationResult(
                classificationType: $model->classification->classification_type,
                bandCode:           $model->classification->band_code,
                passed:             $model->classification->passed,
                personaCode:        $model->classification->persona_code,
                matchScore:         $model->classification->match_score,
            )
            : ClassificationResult::scoreBand($model->maturity_level ?? '', '');

        $questionScores = [];
        foreach ($model->questionScores as $qs) {
            $questionScores[$qs->question_code] = [
                'question_code' => $qs->question_code,
                'feature_code'  => $qs->feature_code,
                'raw'           => $qs->raw_score,
                'final'         => $qs->final_score,
                'selected'      => $qs->selected_options ?? '',
            ];
        }

        return new ScoringResult(
            overallScore:    $model->overall_score,
            assessmentCode:  $model->assessment_code,
            classification:  $classification,
            domainScores:    $domainScores,
            sectionScores:   [],
            signalFlags:     $signalFlags,
            painPoints:      $painPoints,
            recommendations: $recommendations,
            roadmap:         $roadmap,
            weightVersion:   $model->weight_version ?? 1,
            questionScores:  $questionScores,
        );
    }
}

<?php

namespace Modules\Survey\Scoring\Classification;

use Modules\Survey\Models\Persona;
use Modules\Survey\Scoring\AggregatedResult;
use Modules\Survey\Scoring\ClassificationResult;
use Modules\Survey\Scoring\Contracts\ClassificationStrategy;
use Modules\Survey\Scoring\ScoringConfig;

class PersonaMatchClassification implements ClassificationStrategy
{
    /**
     * match_score(persona) = số điều kiện thỏa / tổng điều kiện
     * → chọn persona match_score cao nhất (tie → sort_order thấp hơn)
     */
    public function classify(ScoringConfig $config, AggregatedResult $aggregated, array $signalFlags): ClassificationResult
    {
        $personas = Persona::forAssessment($config->assessmentCode)->with('conditions')->get();

        if ($personas->isEmpty()) {
            return ClassificationResult::none();
        }

        $bestPersona   = null;
        $bestMatchScore = -1.0;

        foreach ($personas as $persona) {
            $conditions  = $persona->conditions;
            $total       = $conditions->count();

            if ($total === 0) {
                continue;
            }

            $matched = 0;
            foreach ($conditions as $cond) {
                if ($this->evaluateCondition($cond, $aggregated, $signalFlags)) {
                    $matched++;
                }
            }

            $matchScore = $matched / $total;

            if ($matchScore > $bestMatchScore) {
                $bestMatchScore = $matchScore;
                $bestPersona    = $persona;
            }
        }

        if ($bestPersona === null || $bestMatchScore <= 0) {
            return ClassificationResult::none();
        }

        return ClassificationResult::persona(
            $bestPersona->persona_code,
            round($bestMatchScore * 100, 2),
            $bestPersona->label,
        );
    }

    private function evaluateCondition($cond, AggregatedResult $aggregated, array $signalFlags): bool
    {
        $value = match ($cond->target_type) {
            'overall'      => $aggregated->overallScore ?? 0.0,
            'domain'       => $aggregated->domainScores[$cond->target_code]?->normalizedScore ?? 0.0,
            'section'      => $aggregated->sectionScores[$cond->target_code]?->normalizedScore ?? 0.0,
            'signal_flag'  => null,
            default        => null,
        };

        if ($cond->target_type === 'signal_flag') {
            $flagValue = $signalFlags[$cond->target_code] ?? false;
            return $flagValue === $cond->flag_value;
        }

        if ($value === null || $cond->threshold_value === null) {
            return false;
        }

        return match ($cond->operator) {
            '<'  => $value < $cond->threshold_value,
            '<=' => $value <= $cond->threshold_value,
            '='  => abs($value - $cond->threshold_value) < 0.001,
            '>=' => $value >= $cond->threshold_value,
            '>'  => $value > $cond->threshold_value,
            default => false,
        };
    }
}

<?php

namespace Modules\Survey\Scoring\Classification;

use Modules\Survey\Models\PassFailConfig;
use Modules\Survey\Scoring\AggregatedResult;
use Modules\Survey\Scoring\ClassificationResult;
use Modules\Survey\Scoring\Contracts\ClassificationStrategy;
use Modules\Survey\Scoring\ScoringConfig;

class PassFailClassification implements ClassificationStrategy
{
    public function classify(ScoringConfig $config, AggregatedResult $aggregated, array $signalFlags): ClassificationResult
    {
        $pfConfig = PassFailConfig::findByCode($config->assessmentCode);

        if ($pfConfig === null) {
            return ClassificationResult::none();
        }

        $score  = $aggregated->overallScore ?? 0.0;
        $passed = $score >= $pfConfig->passing_score;
        $label  = $passed ? $pfConfig->label_pass : $pfConfig->label_fail;

        return ClassificationResult::passFail($passed, $label);
    }
}

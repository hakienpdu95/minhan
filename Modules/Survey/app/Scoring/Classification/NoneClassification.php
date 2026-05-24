<?php

namespace Modules\Survey\Scoring\Classification;

use Modules\Survey\Scoring\AggregatedResult;
use Modules\Survey\Scoring\ClassificationResult;
use Modules\Survey\Scoring\Contracts\ClassificationStrategy;
use Modules\Survey\Scoring\ScoringConfig;

class NoneClassification implements ClassificationStrategy
{
    public function classify(ScoringConfig $config, AggregatedResult $aggregated, array $signalFlags): ClassificationResult
    {
        return ClassificationResult::none();
    }
}

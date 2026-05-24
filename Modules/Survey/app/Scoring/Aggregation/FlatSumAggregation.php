<?php

namespace Modules\Survey\Scoring\Aggregation;

use Modules\Survey\Scoring\AggregatedResult;
use Modules\Survey\Scoring\Contracts\AggregationStrategy;
use Modules\Survey\Scoring\DomainScoreResult;
use Modules\Survey\Scoring\ScoringConfig;

class FlatSumAggregation implements AggregationStrategy
{
    public function aggregate(ScoringConfig $config, array $rawScores, array $weights): AggregatedResult
    {
        $total = array_sum($rawScores);
        $total = max(0.0, min(100.0, (float) $total));

        return new AggregatedResult(
            domainScores: [],
            sectionScores: [],
            overallScore: $total,
        );
    }
}

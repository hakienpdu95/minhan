<?php

namespace Modules\Survey\Scoring\Contracts;

use Modules\Survey\Scoring\AggregatedResult;
use Modules\Survey\Scoring\ScoringConfig;

interface AggregationStrategy
{
    /**
     * Gộp raw feature scores thành domain scores + overall score.
     *
     * @param  array<string, int>  $rawScores   feature_code → raw Fi
     * @param  array<string, float>  $weights   feature_code → Wi
     */
    public function aggregate(ScoringConfig $config, array $rawScores, array $weights): AggregatedResult;
}

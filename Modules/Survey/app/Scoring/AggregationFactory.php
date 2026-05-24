<?php

namespace Modules\Survey\Scoring;

use Modules\Survey\Scoring\Aggregation\FlatSumAggregation;
use Modules\Survey\Scoring\Aggregation\SectionedAggregation;
use Modules\Survey\Scoring\Aggregation\WeightedDomainAggregation;
use Modules\Survey\Scoring\Contracts\AggregationStrategy;

class AggregationFactory
{
    public function make(string $model): AggregationStrategy
    {
        return match ($model) {
            'weighted_domain' => new WeightedDomainAggregation(),
            'sectioned'       => new SectionedAggregation(),
            default           => new FlatSumAggregation(),   // flat_sum + unknown
        };
    }
}

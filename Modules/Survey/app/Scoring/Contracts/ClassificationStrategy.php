<?php

namespace Modules\Survey\Scoring\Contracts;

use Modules\Survey\Scoring\AggregatedResult;
use Modules\Survey\Scoring\ClassificationResult;
use Modules\Survey\Scoring\ScoringConfig;

interface ClassificationStrategy
{
    /**
     * Phân loại người dùng dựa trên kết quả aggregation.
     *
     * @param  array<string, bool>  $signalFlags
     */
    public function classify(ScoringConfig $config, AggregatedResult $aggregated, array $signalFlags): ClassificationResult;
}

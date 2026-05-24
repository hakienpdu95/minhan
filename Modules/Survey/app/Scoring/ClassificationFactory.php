<?php

namespace Modules\Survey\Scoring;

use Modules\Survey\Scoring\Classification\NoneClassification;
use Modules\Survey\Scoring\Classification\PassFailClassification;
use Modules\Survey\Scoring\Classification\PersonaMatchClassification;
use Modules\Survey\Scoring\Classification\ScoreBandClassification;
use Modules\Survey\Scoring\Contracts\ClassificationStrategy;

class ClassificationFactory
{
    public function make(string $type): ClassificationStrategy
    {
        return match ($type) {
            'score_band'    => new ScoreBandClassification(),
            'pass_fail'     => new PassFailClassification(),
            'persona_match' => new PersonaMatchClassification(),
            default         => new NoneClassification(),
        };
    }
}

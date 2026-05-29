<?php

namespace Modules\Assessment\Events;

use Modules\Assessment\Contracts\ScoringSubjectInterface;
use Modules\Assessment\Engine\ScoringResult;
use Modules\Assessment\Models\AssessmentResult;

class AssessmentCompleted
{
    public function __construct(
        public readonly AssessmentResult        $result,
        public readonly ScoringResult           $scoringResult,
        public readonly ScoringSubjectInterface $subject,
    ) {}
}

<?php

namespace Modules\Survey\Scoring;

readonly class JobPositionResult
{
    public function __construct(
        public readonly string $positionCode,
        public readonly string $title,
        public readonly float  $matchScore,
    ) {}

    public function toArray(): array
    {
        return [
            'position_code' => $this->positionCode,
            'title'         => $this->title,
            'match_score'   => round($this->matchScore, 2),
        ];
    }
}

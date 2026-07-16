<?php

namespace Modules\BusinessProject\Data;

use Spatie\LaravelData\Data;

class StageGateConditionData extends Data
{
    public function __construct(
        public readonly string $label,
        public readonly bool $met,
    ) {}
}

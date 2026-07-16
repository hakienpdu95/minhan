<?php

namespace Modules\BusinessProject\Queries\StageGate;

use App\Shared\Contracts\QueryInterface;
use Modules\BusinessProject\Models\BusinessProject;

class CheckStageGateEligibilityQuery implements QueryInterface
{
    public function __construct(
        public readonly BusinessProject $businessProject,
    ) {}
}

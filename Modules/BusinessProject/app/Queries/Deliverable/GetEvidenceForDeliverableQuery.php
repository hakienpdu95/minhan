<?php

namespace Modules\BusinessProject\Queries\Deliverable;

use App\Shared\Contracts\QueryInterface;
use Modules\BusinessProject\Models\Deliverable;

class GetEvidenceForDeliverableQuery implements QueryInterface
{
    public function __construct(
        public readonly Deliverable $deliverable,
    ) {}
}

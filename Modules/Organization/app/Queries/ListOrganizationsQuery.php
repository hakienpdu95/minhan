<?php

namespace Modules\Organization\Queries;

use App\Shared\Contracts\QueryInterface;

class ListOrganizationsQuery implements QueryInterface
{
    public function __construct(
        public readonly int $perPage = 20,
        public readonly string $orderBy = 'created_at',
        public readonly string $direction = 'desc',
    ) {}
}

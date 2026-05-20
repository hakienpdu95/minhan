<?php

namespace Modules\Organization\Queries;

use App\Shared\Contracts\QueryInterface;

class ListOrganizationsQuery implements QueryInterface
{
    public function __construct(
        public readonly int    $page      = 1,
        public readonly int    $perPage   = 25,
        public readonly string $sortField = 'created_at',
        public readonly string $sortDir   = 'desc',
        public readonly ?string $name          = null,
        public readonly ?string $provinceCode  = null,
        public readonly ?string $wardCode      = null,
    ) {}
}

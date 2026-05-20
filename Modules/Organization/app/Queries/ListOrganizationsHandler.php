<?php

namespace Modules\Organization\Queries;

use App\Shared\Contracts\QueryHandlerInterface;
use App\Shared\Contracts\QueryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Organization\Models\Organization;

class ListOrganizationsHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): LengthAwarePaginator
    {
        /** @var ListOrganizationsQuery $query */
        return Organization::withoutTenant()
            ->withCount('members')
            ->orderBy($query->orderBy, $query->direction)
            ->paginate($query->perPage);
    }
}

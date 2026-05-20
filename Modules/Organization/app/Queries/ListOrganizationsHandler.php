<?php

namespace Modules\Organization\Queries;

use App\Shared\Contracts\QueryHandlerInterface;
use App\Shared\Contracts\QueryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Organization\Models\Organization;

class ListOrganizationsHandler implements QueryHandlerInterface
{
    private const SORTABLE = [
        'name', 'industry', 'status', 'members_count', 'province_name', 'created_at',
    ];

    public function handle(QueryInterface $query): LengthAwarePaginator
    {
        /** @var ListOrganizationsQuery $query */

        $sortField = in_array($query->sortField, self::SORTABLE, true)
            ? $query->sortField
            : 'created_at';

        $sortDir = $query->sortDir === 'asc' ? 'asc' : 'desc';

        $q = Organization::withoutTenant()
            ->withCount('members')
            ->with(['province:province_code,name', 'ward:ward_code,name']);

        if ($query->name !== null && $query->name !== '') {
            $q->where('organizations.name', 'like', '%' . $query->name . '%');
        }

        if ($query->provinceCode !== null && $query->provinceCode !== '') {
            $q->where('organizations.province_code', $query->provinceCode);
        }

        if ($query->wardCode !== null && $query->wardCode !== '') {
            $q->where('organizations.ward_code', $query->wardCode);
        }

        // members_count sort requires aggregate alias
        if ($sortField === 'members_count') {
            $q->orderBy('members_count', $sortDir);
        } elseif ($sortField === 'province_name') {
            $q->orderBy('province_code', $sortDir);
        } else {
            $q->orderBy('organizations.' . $sortField, $sortDir);
        }

        return $q->paginate($query->perPage, ['*'], 'page', $query->page);
    }
}

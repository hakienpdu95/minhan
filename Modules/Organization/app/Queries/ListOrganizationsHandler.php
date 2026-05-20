<?php

namespace Modules\Organization\Queries;

use App\Shared\Contracts\QueryHandlerInterface;
use App\Shared\Contracts\QueryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
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

        // ── Text search across multiple fields (OR) ──────────────────
        if ($query->search !== null && $query->search !== '') {
            $term = '%' . $query->search . '%';
            $q->where(function (Builder $sub) use ($term): void {
                $sub->where('organizations.name',     'like', $term)
                    ->orWhere('organizations.tax_code', 'like', $term)
                    ->orWhere('organizations.email',    'like', $term)
                    ->orWhere('organizations.phone',    'like', $term);
            });
        }

        // ── Exact filters ────────────────────────────────────────────
        if ($query->provinceCode !== null && $query->provinceCode !== '') {
            $q->where('organizations.province_code', $query->provinceCode);
        }

        if ($query->wardCode !== null && $query->wardCode !== '') {
            $q->where('organizations.ward_code', $query->wardCode);
        }

        // ── Date range on created_at ─────────────────────────────────
        if ($query->dateFrom !== null && $query->dateFrom !== '') {
            $q->whereDate('organizations.created_at', '>=', $query->dateFrom);
        }

        if ($query->dateTo !== null && $query->dateTo !== '') {
            $q->whereDate('organizations.created_at', '<=', $query->dateTo);
        }

        // ── Sort ─────────────────────────────────────────────────────
        match ($sortField) {
            'members_count' => $q->orderBy('members_count', $sortDir),
            'province_name' => $q->orderBy('organizations.province_code', $sortDir),
            default         => $q->orderBy('organizations.' . $sortField, $sortDir),
        };

        return $q->paginate($query->perPage, ['*'], 'page', $query->page);
    }
}

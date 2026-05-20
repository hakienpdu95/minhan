<?php

namespace Modules\User\Queries;

use App\Models\User;
use App\Shared\Contracts\QueryHandlerInterface;
use App\Shared\Contracts\QueryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class ListUsersHandler implements QueryHandlerInterface
{
    private const SORTABLE = [
        'name', 'email', 'department', 'is_active', 'created_at', 'organization_name',
    ];

    public function handle(QueryInterface $query): LengthAwarePaginator
    {
        /** @var ListUsersQuery $query */

        $sortField = in_array($query->sortField, self::SORTABLE, true)
            ? $query->sortField
            : 'created_at';

        $sortDir = $query->sortDir === 'asc' ? 'asc' : 'desc';

        $q = User::query()
            ->select('users.*')
            ->whereNotNull('users.organization_id')
            ->with(['organization:id,name', 'organizationMembership']);

        // ── Tenant scope ─────────────────────────────────────────────
        if ($query->organizationId !== null) {
            $q->where('users.organization_id', $query->organizationId);
        }

        // ── Text search (OR) ─────────────────────────────────────────
        if ($query->search !== null && $query->search !== '') {
            $term = '%' . $query->search . '%';
            $q->where(function (Builder $sub) use ($term): void {
                $sub->where('users.name',  'like', $term)
                    ->orWhere('users.email', 'like', $term);
            });
        }

        // ── Role filter (via organizationMembership) ─────────────────
        if ($query->role !== null && $query->role !== '') {
            $role = $query->role;
            $q->whereHas('organizationMembership', function (Builder $sub) use ($role): void {
                $sub->where('role', $role);
            });
        }

        // ── Status filter ─────────────────────────────────────────────
        if ($query->status !== null && $query->status !== '') {
            $q->where('users.is_active', $query->status === '1');
        }

        // ── Date range ────────────────────────────────────────────────
        if ($query->dateFrom !== null && $query->dateFrom !== '') {
            $q->whereDate('users.created_at', '>=', $query->dateFrom);
        }

        if ($query->dateTo !== null && $query->dateTo !== '') {
            $q->whereDate('users.created_at', '<=', $query->dateTo);
        }

        // ── Sort ──────────────────────────────────────────────────────
        match ($sortField) {
            'organization_name' => $q->leftJoin('organizations as org_sort', 'users.organization_id', '=', 'org_sort.id')
                                      ->orderBy('org_sort.name', $sortDir),
            default             => $q->orderBy('users.' . $sortField, $sortDir),
        };

        return $q->paginate($query->perPage, ['*'], 'page', $query->page);
    }
}

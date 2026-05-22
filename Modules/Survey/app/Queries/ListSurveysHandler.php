<?php

namespace Modules\Survey\Queries;

use App\Shared\Contracts\QueryHandlerInterface;
use App\Shared\Contracts\QueryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Modules\Survey\Models\Survey;

class ListSurveysHandler implements QueryHandlerInterface
{
    private const SORTABLE = ['title', 'status', 'version', 'responses_count', 'created_at'];

    public function handle(QueryInterface $query): LengthAwarePaginator
    {
        /** @var ListSurveysQuery $query */

        $sortField = in_array($query->sortField, self::SORTABLE, true)
            ? $query->sortField
            : 'created_at';

        $sortDir = $query->sortDir === 'asc' ? 'asc' : 'desc';

        $q = Survey::withCount('responses');

        // ── Text search: title OR slug ────────────────────────────────
        if ($query->search !== null && $query->search !== '') {
            $term = '%' . $query->search . '%';
            $q->where(function (Builder $sub) use ($term): void {
                $sub->where('title', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        // ── Status filter ─────────────────────────────────────────────
        if ($query->status !== null) {
            $q->where('status', $query->status);
        }

        // ── Date range on created_at ──────────────────────────────────
        if ($query->dateFrom !== null && $query->dateFrom !== '') {
            $q->whereDate('created_at', '>=', $query->dateFrom);
        }

        if ($query->dateTo !== null && $query->dateTo !== '') {
            $q->whereDate('created_at', '<=', $query->dateTo);
        }

        // ── Sort ──────────────────────────────────────────────────────
        // responses_count is a SELECT alias added by withCount — no table prefix needed.
        // All other columns are unambiguous (no JOINs in this query).
        $q->orderBy($sortField, $sortDir);

        return $q->paginate($query->perPage, ['*'], 'page', $query->page);
    }
}

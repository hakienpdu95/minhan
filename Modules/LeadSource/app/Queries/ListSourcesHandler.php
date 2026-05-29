<?php

namespace Modules\LeadSource\Queries;

use App\Shared\Contracts\QueryHandlerInterface;
use App\Shared\Contracts\QueryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\LeadSource\Models\LeadSource;

class ListSourcesHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): Collection
    {
        /** @var ListSourcesQuery $query */
        $orgId      = $query->orgId;
        $activeOnly = $query->activeOnly;
        $cacheKey   = "lead_sources:{$orgId}" . ($activeOnly ? '' : ':all');
        $ttl        = config('lead_source.cache_ttl', 600);

        $loader = function () use ($orgId, $activeOnly) {
            $q = LeadSource::query()
                ->where(function ($q) use ($orgId) {
                    $q->where('organization_id', $orgId)
                      ->orWhere('is_global', true);
                })
                ->orderBy('sort_order');

            if ($activeOnly) {
                $q->where('is_active', true);
            }

            return $q->get();
        };

        try {
            return Cache::tags(["org:{$orgId}", 'sources'])->remember($cacheKey, $ttl, $loader);
        } catch (\BadMethodCallException) {
            return Cache::remember($cacheKey, $ttl, $loader);
        }
    }
}

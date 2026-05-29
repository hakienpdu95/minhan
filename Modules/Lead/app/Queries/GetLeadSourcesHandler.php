<?php

namespace Modules\Lead\Queries;

use App\Shared\Contracts\QueryHandlerInterface;
use App\Shared\Contracts\QueryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\LeadSource\Models\LeadSource;

class GetLeadSourcesHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): Collection
    {
        /** @var GetLeadSourcesQuery $query */
        $orgId    = $query->orgId;
        $cacheKey = "lead_sources:{$orgId}";
        $ttl      = config('lead.cache_ttl.lead_sources', 600);

        $loader = fn () => LeadSource::query()
            ->where(function ($q) use ($orgId) {
                $q->where('organization_id', $orgId)
                  ->orWhere('is_global', true);
            })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        try {
            return Cache::tags(["org:{$orgId}", 'sources'])->remember($cacheKey, $ttl, $loader);
        } catch (\BadMethodCallException) {
            return Cache::remember($cacheKey, $ttl, $loader);
        }
    }
}

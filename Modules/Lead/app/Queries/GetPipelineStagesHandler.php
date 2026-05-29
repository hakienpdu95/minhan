<?php

namespace Modules\Lead\Queries;

use App\Shared\Contracts\QueryHandlerInterface;
use App\Shared\Contracts\QueryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\LeadPipelineStage\Models\LeadPipelineStage;

class GetPipelineStagesHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): Collection
    {
        /** @var GetPipelineStagesQuery $query */
        $orgId    = $query->orgId;
        $cacheKey = "pipeline_stages:{$orgId}";
        $ttl      = config('lead.cache_ttl.pipeline_stages', 600);

        $loader = fn () => LeadPipelineStage::query()
            ->where(function ($q) use ($orgId) {
                $q->where('organization_id', $orgId)
                  ->orWhere('is_global', true);
            })
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        try {
            return Cache::tags(["org:{$orgId}", 'pipeline'])->remember($cacheKey, $ttl, $loader);
        } catch (\BadMethodCallException) {
            return Cache::remember($cacheKey, $ttl, $loader);
        }
    }
}

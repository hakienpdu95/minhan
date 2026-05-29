<?php

namespace Modules\LeadPipelineStage\Queries;

use App\Shared\Contracts\QueryHandlerInterface;
use App\Shared\Contracts\QueryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\LeadPipelineStage\Models\LeadPipelineStage;

class ListStagesHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): Collection
    {
        /** @var ListStagesQuery $query */
        $orgId      = $query->orgId;
        $activeOnly = $query->activeOnly;
        $cacheKey   = "pipeline_stages:{$orgId}" . ($activeOnly ? '' : ':all');
        $ttl        = config('lead_pipeline_stage.cache_ttl', 600);

        $loader = function () use ($orgId, $activeOnly) {
            $q = LeadPipelineStage::query()
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
            return Cache::tags(["org:{$orgId}", 'pipeline'])->remember($cacheKey, $ttl, $loader);
        } catch (\BadMethodCallException) {
            return Cache::remember($cacheKey, $ttl, $loader);
        }
    }
}

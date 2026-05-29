<?php

namespace Modules\Lead\Queries;

use App\Shared\Contracts\QueryHandlerInterface;
use App\Shared\Contracts\QueryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Lead\Models\LeadTagDefinition;

class ListTagsHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): Collection
    {
        /** @var ListTagsQuery $query */
        $orgId = $query->orgId;
        $key   = "lead_tags:{$orgId}";

        try {
            return Cache::tags(["org:{$orgId}", 'lead_tags'])
                ->remember($key, config('lead.cache_ttl.lead_tags', 600), fn () => $this->fetch($orgId));
        } catch (\BadMethodCallException) {
            return Cache::remember($key, config('lead.cache_ttl.lead_tags', 600), fn () => $this->fetch($orgId));
        }
    }

    private function fetch(int $orgId): Collection
    {
        return LeadTagDefinition::where('organization_id', $orgId)
            ->orderBy('name')
            ->get();
    }
}

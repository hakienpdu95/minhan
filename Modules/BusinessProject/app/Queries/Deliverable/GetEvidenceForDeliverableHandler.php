<?php

namespace Modules\BusinessProject\Queries\Deliverable;

use App\Shared\Contracts\QueryHandlerInterface;
use App\Shared\Contracts\QueryInterface;
use Illuminate\Database\Eloquent\Collection;

/**
 * Chưa có route/UI gọi tới ở Vertical Slice 1 — Diagnosis Workspace (Phase 2) mới
 * thao tác đính evidence. Tồn tại sẵn để không phải migrate/thiết kế lại quan hệ khi đó.
 */
class GetEvidenceForDeliverableHandler implements QueryHandlerInterface
{
    public function handle(QueryInterface $query): Collection
    {
        /** @var GetEvidenceForDeliverableQuery $query */
        return $query->deliverable->evidenceFor()->get();
    }
}

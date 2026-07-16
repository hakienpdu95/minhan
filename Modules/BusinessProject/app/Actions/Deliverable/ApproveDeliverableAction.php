<?php

namespace Modules\BusinessProject\Actions\Deliverable;

use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Events\DeliverableApproved;
use Modules\BusinessProject\Models\Deliverable;

class ApproveDeliverableAction
{
    use AsAction;

    public function handle(Deliverable $deliverable, ?string $comment = null): Deliverable
    {
        Gate::authorize('approve', $deliverable);

        // approve() gọi onApprovalCompleted() trên Deliverable (đồng bộ status=approved)
        // khi đây là bước duyệt cuối cùng của flow — xem Deliverable::onApprovalCompleted().
        $deliverable->approve($comment);

        $deliverable->refresh();

        event(new DeliverableApproved($deliverable));

        return $deliverable;
    }
}

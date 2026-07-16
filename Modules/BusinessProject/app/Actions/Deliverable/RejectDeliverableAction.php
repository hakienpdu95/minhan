<?php

namespace Modules\BusinessProject\Actions\Deliverable;

use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Events\DeliverableRejected;
use Modules\BusinessProject\Models\Deliverable;

class RejectDeliverableAction
{
    use AsAction;

    public function handle(Deliverable $deliverable, ?string $comment = null): Deliverable
    {
        Gate::authorize('approve', $deliverable);

        $deliverable->reject($comment);

        $deliverable->refresh();
        $deliverable->status = DeliverableStatus::Rejected->value;
        $deliverable->save();

        event(new DeliverableRejected($deliverable, $comment));

        return $deliverable;
    }
}

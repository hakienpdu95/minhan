<?php

namespace Modules\BusinessProject\Actions\Delivery;

use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Enums\ChangeRequestStatus;
use Modules\BusinessProject\Events\ChangeRequestRejected;
use Modules\BusinessProject\Models\ChangeRequest;

class RejectChangeRequestAction
{
    use AsAction;

    public function handle(ChangeRequest $changeRequest, ?string $comment = null): ChangeRequest
    {
        Gate::authorize('approve', $changeRequest);

        $changeRequest->reject($comment);

        $changeRequest->refresh();
        $changeRequest->status = ChangeRequestStatus::Rejected->value;
        $changeRequest->save();

        event(new ChangeRequestRejected($changeRequest, $comment));

        return $changeRequest;
    }
}

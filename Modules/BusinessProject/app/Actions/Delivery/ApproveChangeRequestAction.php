<?php

namespace Modules\BusinessProject\Actions\Delivery;

use Illuminate\Support\Facades\Gate;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Events\ChangeRequestApproved;
use Modules\BusinessProject\Models\ChangeRequest;

class ApproveChangeRequestAction
{
    use AsAction;

    public function handle(ChangeRequest $changeRequest, ?string $comment = null): ChangeRequest
    {
        Gate::authorize('approve', $changeRequest);

        // approve() gọi onApprovalCompleted() trên ChangeRequest (đồng bộ status=approved +
        // mở khóa lại SOW nếu impacts_scope) — xem ChangeRequest::onApprovalCompleted().
        $changeRequest->approve($comment);

        $changeRequest->refresh();

        event(new ChangeRequestApproved($changeRequest));

        return $changeRequest;
    }
}

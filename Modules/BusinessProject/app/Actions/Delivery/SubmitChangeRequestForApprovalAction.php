<?php

namespace Modules\BusinessProject\Actions\Delivery;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Enums\ChangeRequestStatus;
use Modules\BusinessProject\Events\ChangeRequestSubmittedForApproval;
use Modules\BusinessProject\Models\ChangeRequest;
use Modules\BusinessProject\Notifications\ChangeRequestAwaitingApprovalNotification;

class SubmitChangeRequestForApprovalAction
{
    use AsAction;

    public function handle(ChangeRequest $changeRequest): ChangeRequest
    {
        $changeRequest->submit();

        $changeRequest->refresh();
        $changeRequest->status = ChangeRequestStatus::Submitted->value;
        $changeRequest->save();

        $approvers = User::role([RoleEnum::LEAD_CONSULTANT->value, RoleEnum::CEO->value])->get();
        Notification::send($approvers, new ChangeRequestAwaitingApprovalNotification($changeRequest));

        event(new ChangeRequestSubmittedForApproval($changeRequest));

        return $changeRequest;
    }
}

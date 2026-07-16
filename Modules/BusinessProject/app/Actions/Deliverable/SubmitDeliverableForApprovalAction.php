<?php

namespace Modules\BusinessProject\Actions\Deliverable;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\BusinessProject\Enums\DeliverableStatus;
use Modules\BusinessProject\Events\DeliverableSubmittedForApproval;
use Modules\BusinessProject\Models\Deliverable;
use Modules\BusinessProject\Notifications\DeliverableAwaitingApprovalNotification;

class SubmitDeliverableForApprovalAction
{
    use AsAction;

    public function handle(Deliverable $deliverable): Deliverable
    {
        // Đúng nguyên tắc "1 Approval Service — dùng mọi nơi": gọi trực tiếp
        // Ringlesoft (Approvable trait) thay vì tự chế cột status duyệt riêng.
        $deliverable->submit();

        $deliverable->refresh();
        $deliverable->status = DeliverableStatus::Submitted->value;
        $deliverable->save();

        $approvers = User::role([RoleEnum::LEAD_CONSULTANT->value, RoleEnum::CEO->value])->get();
        Notification::send($approvers, new DeliverableAwaitingApprovalNotification($deliverable));

        event(new DeliverableSubmittedForApproval($deliverable));

        return $deliverable;
    }
}

<?php

namespace Modules\BusinessProject\Notifications;

use App\Notifications\Concerns\RespectsNotificationPreferences;
use App\Shared\Notifications\NotificationData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\BusinessProject\Models\ChangeRequest;

class ChangeRequestAwaitingApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsNotificationPreferences;

    public function __construct(private readonly ChangeRequest $changeRequest) {}

    protected function notificationType(): string
    {
        return 'business_project_change_request_awaiting_approval';
    }

    public function toDatabase(object $notifiable): array
    {
        $project = $this->changeRequest->businessProject;

        return NotificationData::make(
            type: 'business_project_change_request_awaiting_approval',
            title: "Change Request \"{$this->changeRequest->title}\" đang chờ phê duyệt",
            body: "Business Project \"{$project->name}\" có Change Request cần bạn phê duyệt.",
            url: route('backend.business-projects.delivery.show', $project->uuid),
            icon: 'alert',
            severity: 'warning',
            meta: [
                'business_project_id' => $project->id,
                'change_request_id' => $this->changeRequest->id,
            ],
        );
    }
}

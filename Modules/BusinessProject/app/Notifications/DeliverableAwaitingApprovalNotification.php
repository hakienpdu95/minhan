<?php

namespace Modules\BusinessProject\Notifications;

use App\Notifications\Concerns\RespectsNotificationPreferences;
use App\Shared\Notifications\NotificationData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\BusinessProject\Models\Deliverable;

class DeliverableAwaitingApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsNotificationPreferences;

    public function __construct(private readonly Deliverable $deliverable) {}

    protected function notificationType(): string
    {
        return 'business_project_deliverable_awaiting_approval';
    }

    public function toDatabase(object $notifiable): array
    {
        $project = $this->deliverable->businessProject;

        return NotificationData::make(
            type: 'business_project_deliverable_awaiting_approval',
            title: "\"{$this->deliverable->title}\" đang chờ phê duyệt",
            body: "Business Project \"{$project->name}\" có deliverable \"{$this->deliverable->title}\" cần bạn phê duyệt.",
            url: route('backend.business-projects.show', $project->uuid),
            icon: 'task',
            severity: 'info',
            meta: [
                'business_project_id' => $project->id,
                'deliverable_id' => $this->deliverable->id,
            ],
        );
    }
}

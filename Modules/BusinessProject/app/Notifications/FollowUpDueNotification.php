<?php

namespace Modules\BusinessProject\Notifications;

use App\Notifications\Concerns\RespectsNotificationPreferences;
use App\Shared\Notifications\NotificationData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\BusinessProject\Models\SuccessReview;

class FollowUpDueNotification extends Notification implements ShouldQueue
{
    use Queueable, RespectsNotificationPreferences;

    public function __construct(private readonly SuccessReview $successReview) {}

    protected function notificationType(): string
    {
        return 'success_followup_due';
    }

    public function toDatabase(object $notifiable): array
    {
        $project = $this->successReview->businessProject;
        $due = $this->successReview->follow_up_at?->format('d/m/Y') ?? '—';

        return NotificationData::make(
            type: 'success_followup_due',
            title: "Follow-up đến hạn: {$project->name}",
            body: "Đã đến hạn follow-up ({$due}) với khách hàng của dự án \"{$project->name}\".",
            url: route('backend.business-projects.customer-success.show', $project),
            icon: 'bell',
            severity: 'warning',
            meta: [
                'business_project_id' => $project->id,
                'success_review_id' => $this->successReview->id,
                'follow_up_at' => $this->successReview->follow_up_at?->toDateString(),
            ],
        );
    }
}

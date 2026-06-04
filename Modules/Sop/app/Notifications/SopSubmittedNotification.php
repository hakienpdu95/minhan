<?php

namespace Modules\Sop\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Sop\Models\SopProcess;
use Modules\Sop\Models\SopVersion;

/**
 * Gửi cho approver ở step_order = 1 khi SOP được submit để duyệt.
 */
class SopSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly SopProcess $sop,
        private readonly SopVersion $version,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'           => 'sop_submitted',
            'sop_id'         => $this->sop->id,
            'sop_uuid'       => $this->sop->uuid,
            'sop_code'       => $this->sop->code,
            'sop_title'      => $this->sop->title,
            'version_number' => $this->version->version_number,
            'version_uuid'   => $this->version->uuid,
            'change_summary' => $this->version->change_summary,
            'message'        => "SOP [{$this->sop->code}] \"{$this->sop->title}\" v{$this->version->version_number} đang chờ duyệt.",
            'url'            => route('backend.sop.pending-approvals'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reviewUrl = route('backend.sop.versions.review', [
            'sop'     => $this->sop->uuid,
            'version' => $this->version->uuid,
        ]);

        return (new MailMessage)
            ->subject("SOP cần duyệt: [{$this->sop->code}] {$this->sop->title}")
            ->greeting("Xin chào {$notifiable->name},")
            ->line("SOP **[{$this->sop->code}] {$this->sop->title}** (phiên bản v{$this->version->version_number}) vừa được gửi để duyệt.")
            ->when($this->version->change_summary, fn ($mail) => $mail->line("**Tóm tắt thay đổi:** {$this->version->change_summary}"))
            ->action('Xem và duyệt SOP', $reviewUrl)
            ->line('Vui lòng xem xét và thực hiện hành động duyệt / từ chối trong hệ thống.');
    }
}

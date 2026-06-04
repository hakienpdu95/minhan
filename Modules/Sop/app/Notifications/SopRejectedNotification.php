<?php

namespace Modules\Sop\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Sop\Models\SopProcess;
use Modules\Sop\Models\SopVersion;

/**
 * Gửi cho creator khi version bị từ chối, kèm comment của người reject.
 */
class SopRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly SopProcess $sop,
        private readonly SopVersion $version,
        private readonly string     $comment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'           => 'sop_rejected',
            'sop_id'         => $this->sop->id,
            'sop_uuid'       => $this->sop->uuid,
            'sop_code'       => $this->sop->code,
            'sop_title'      => $this->sop->title,
            'version_number' => $this->version->version_number,
            'version_uuid'   => $this->version->uuid,
            'comment'        => $this->comment,
            'message'        => "SOP [{$this->sop->code}] \"{$this->sop->title}\" v{$this->version->version_number} đã bị từ chối.",
            'url'            => route('backend.sop.show', $this->sop->uuid),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sopUrl = route('backend.sop.show', $this->sop->uuid);

        return (new MailMessage)
            ->subject("SOP bị từ chối: [{$this->sop->code}] {$this->sop->title}")
            ->greeting("Xin chào {$notifiable->name},")
            ->line("SOP **[{$this->sop->code}] {$this->sop->title}** phiên bản v{$this->version->version_number} đã bị từ chối.")
            ->line("**Lý do từ chối:** {$this->comment}")
            ->line('Vui lòng chỉnh sửa và gửi lại để duyệt.')
            ->action('Xem và chỉnh sửa SOP', $sopUrl);
    }
}

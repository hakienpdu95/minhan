<?php

namespace Modules\Sop\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Sop\Models\SopProcess;

/**
 * Gửi cho owner khi SOP sắp hết hạn trong 7 ngày.
 */
class SopExpiryWarningNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly SopProcess $sop,
        private readonly int        $daysLeft,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'         => 'sop_expiry_warning',
            'sop_id'       => $this->sop->id,
            'sop_uuid'     => $this->sop->uuid,
            'sop_code'     => $this->sop->code,
            'sop_title'    => $this->sop->title,
            'expired_date' => $this->sop->expired_date?->toDateString(),
            'days_left'    => $this->daysLeft,
            'message'      => "SOP [{$this->sop->code}] \"{$this->sop->title}\" sẽ hết hạn trong {$this->daysLeft} ngày ({$this->sop->expired_date?->format('d/m/Y')}).",
            'url'          => route('backend.sop.show', $this->sop->uuid),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $sopUrl = route('backend.sop.show', $this->sop->uuid);

        return (new MailMessage)
            ->subject("SOP sắp hết hạn: [{$this->sop->code}] {$this->sop->title}")
            ->greeting("Xin chào {$notifiable->name},")
            ->line("SOP **[{$this->sop->code}] {$this->sop->title}** sẽ hết hạn vào ngày **{$this->sop->expired_date?->format('d/m/Y')}** ({$this->daysLeft} ngày nữa).")
            ->line('Vui lòng xem xét và gia hạn hoặc thay thế SOP trước khi hết hạn.')
            ->action('Xem SOP', $sopUrl);
    }
}

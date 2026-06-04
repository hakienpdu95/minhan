<?php

namespace Modules\KcItem\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\KcItem\Models\KcItem;

class KcItemExpiringSoonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly KcItem $kcItem,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'item_id'      => $this->kcItem->id,
            'title'        => $this->kcItem->title,
            'expired_date' => $this->kcItem->expired_date?->format('d/m/Y'),
            'url'          => route('backend.kc-items.show', $this->kcItem->id),
            'message'      => 'Tài liệu "' . $this->kcItem->title . '" sẽ hết hiệu lực vào '
                              . $this->kcItem->expired_date?->format('d/m/Y') . '.',
        ];
    }
}

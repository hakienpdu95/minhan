<?php

namespace Modules\KcItem\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Modules\KcItem\Models\KcItem;

class KcItemApprovedNotification extends Notification implements ShouldQueue
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
            'item_id' => $this->kcItem->id,
            'title'   => $this->kcItem->title,
            'url'     => route('backend.kc-items.show', $this->kcItem->id),
            'message' => 'Tài liệu "' . $this->kcItem->title . '" đã được duyệt.',
        ];
    }
}

<?php

namespace Modules\KcItem\Listeners;

use Modules\KcItem\Events\KcItemUpdated;

class LogKcItemUpdated
{
    public function handle(KcItemUpdated $event): void
    {
        activity()->on($event->kcItem)->log('kc_item.updated');
    }
}

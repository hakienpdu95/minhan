<?php

namespace Modules\KcItem\Listeners;

use Modules\KcItem\Events\KcItemCreated;

class LogKcItemCreated
{
    public function handle(KcItemCreated $event): void
    {
        activity()->on($event->kcItem)->log('kc_item.created');
    }
}

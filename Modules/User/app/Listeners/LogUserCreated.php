<?php

namespace Modules\User\Listeners;

use Modules\User\Events\UserCreated;

class LogUserCreated
{
    public function handle(UserCreated $event): void
    {
        activity()->on($event->user)->log('user.created');
    }
}

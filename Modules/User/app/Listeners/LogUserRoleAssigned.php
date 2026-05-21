<?php

namespace Modules\User\Listeners;

use Modules\User\Events\UserRoleAssigned;

class LogUserRoleAssigned
{
    public function handle(UserRoleAssigned $event): void
    {
        activity()
            ->on($event->user)
            ->withProperties(['role' => $event->role])
            ->log('user.role_assigned');
    }
}

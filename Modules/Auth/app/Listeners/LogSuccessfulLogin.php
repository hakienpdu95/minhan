<?php

namespace Modules\Auth\Listeners;

use App\Shared\Support\ActivityLogger;
use Illuminate\Auth\Events\Login;

/**
 * Ghi audit log mỗi khi user login thành công.
 * Đăng ký trong Modules\Auth\Providers\EventServiceProvider.
 */
class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        ActivityLogger::on('auth')
            ->event('login')
            ->performedOn($event->user)
            ->withProperties([
                'ip'         => request()->ip(),
                'user_agent' => request()->userAgent(),
                'remember'   => $event->remember,
            ])
            ->log('login');
    }
}

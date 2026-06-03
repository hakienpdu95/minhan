<?php

namespace Modules\Employee\Listeners;

use Modules\Employee\Events\EmployeeUpdated;

class LogEmployeeUpdated
{
    public function handle(EmployeeUpdated $event): void
    {
        activity()
            ->on($event->employee)
            ->log('employee.updated');
    }
}

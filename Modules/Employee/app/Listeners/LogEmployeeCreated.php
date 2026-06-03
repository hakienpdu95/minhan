<?php

namespace Modules\Employee\Listeners;

use Modules\Employee\Events\EmployeeCreated;

class LogEmployeeCreated
{
    public function handle(EmployeeCreated $event): void
    {
        activity()
            ->on($event->employee)
            ->log('employee.created');
    }
}

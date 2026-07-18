<?php

namespace Modules\Employee\Policies;

use App\Models\User;
use Modules\Employee\Models\Employee;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'hr', 'ceo', 'viewer', 'ops']);
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'hr', 'ceo', 'viewer', 'ops']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'hr']);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'hr']);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin']);
    }

    public function transfer(User $user, Employee $employee): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'hr']);
    }

    public function offboard(User $user, Employee $employee): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'hr']);
    }

    public function viewSalary(User $user, Employee $employee): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'hr']);
    }

    public function updateSalary(User $user, Employee $employee): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'hr']);
    }
}

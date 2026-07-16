<?php

namespace Modules\Department\Policies;

use App\Models\User;
use Modules\Department\Models\Department;

/**
 * Authorization policy cho Department resource.
 * Super-admin bypass toàn bộ qua Gate::before() trong AppServiceProvider.
 */
class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'ceo', 'viewer', 'ops', 'marketing']);
    }

    public function view(User $user, Department $department): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'ceo', 'viewer', 'ops', 'marketing']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr']);
    }

    public function update(User $user, Department $department): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr']);
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->hasRole('system_admin');
    }
}

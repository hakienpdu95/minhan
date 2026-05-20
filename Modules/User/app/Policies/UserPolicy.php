<?php

namespace Modules\User\Policies;

use App\Models\User;

class UserPolicy
{
    /** Super-admin bypass handled by Gate::before() in AppServiceProvider. */

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'System_Admin', 'CEO', 'HR']);
    }

    public function view(User $user, User $target): bool
    {
        if ($user->hasAnyRole(['super-admin', 'System_Admin'])) {
            return true;
        }

        // Other authorized roles can only view users in their own org
        if ($user->hasAnyRole(['CEO', 'HR'])) {
            return $user->organization_id === $target->organization_id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'System_Admin', 'HR']);
    }

    public function update(User $user, User $target): bool
    {
        if ($user->hasAnyRole(['super-admin', 'System_Admin'])) {
            return true;
        }

        if ($user->hasRole('HR')) {
            return $user->organization_id === $target->organization_id;
        }

        return false;
    }

    public function delete(User $user, User $target): bool
    {
        return $user->hasAnyRole(['super-admin', 'System_Admin']);
    }
}

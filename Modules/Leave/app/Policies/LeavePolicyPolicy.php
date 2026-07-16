<?php

namespace Modules\Leave\Policies;

use App\Models\User;
use Modules\Leave\Models\LeavePolicy;

class LeavePolicyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'ceo']);
    }

    public function view(User $user, LeavePolicy $policy): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'ceo']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr']);
    }

    public function update(User $user, LeavePolicy $policy): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr']);
    }

    public function delete(User $user, LeavePolicy $policy): bool
    {
        return $user->hasRole('system_admin');
    }
}

<?php

namespace Modules\Leave\Policies;

use App\Models\User;
use Modules\Leave\Models\LeaveRequest;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'ceo', 'manager', 'sales', 'ops']);
    }

    public function view(User $user, LeaveRequest $request): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'ceo', 'manager'])
            || $user->id === $request->created_by;
    }

    public function create(User $user): bool
    {
        return true; // All authenticated users can create leave requests
    }

    public function approve(User $user, LeaveRequest $request): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'manager', 'ceo']);
    }

    public function reject(User $user, LeaveRequest $request): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'manager', 'ceo']);
    }

    public function cancel(User $user, LeaveRequest $request): bool
    {
        return $user->id === $request->created_by
            || $user->hasAnyRole(['system_admin', 'hr']);
    }
}

<?php

namespace Modules\KpiGoal\Policies;

use App\Models\User;
use Modules\KpiGoal\Models\KpiGoal;

class KpiGoalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'ceo', 'manager', 'sales', 'ops']);
    }

    public function view(User $user, KpiGoal $goal): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'ceo', 'manager'])
            || $user->id === $goal->created_by;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'manager', 'ceo']);
    }

    public function update(User $user, KpiGoal $goal): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'manager', 'ceo'])
            && $goal->isEditable();
    }

    public function approve(User $user, KpiGoal $goal): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'manager', 'ceo']);
    }

    public function updateProgress(User $user, KpiGoal $goal): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'manager', 'ceo'])
            || $user->id === $goal->created_by;
    }

    public function closeCycle(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'ceo']);
    }

    public function viewLeaderboard(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'hr', 'ceo', 'manager']);
    }

    public function delete(User $user, KpiGoal $goal): bool
    {
        return $user->hasRole('system_admin');
    }
}

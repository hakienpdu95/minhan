<?php

namespace Modules\Project\Policies;

use App\Models\User;
use Modules\Project\Models\Project;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'ceo', 'hr', 'ops', 'sales', 'marketing', 'viewer']);
    }

    public function view(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['system_admin', 'ceo', 'hr', 'ops', 'sales', 'marketing', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'ceo', 'hr', 'ops']);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['system_admin', 'ceo', 'hr', 'ops']);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->hasAnyRole(['system_admin', 'ceo']);
    }
}

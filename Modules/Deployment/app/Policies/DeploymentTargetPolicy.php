<?php

namespace Modules\Deployment\Policies;

use App\Models\User;
use Modules\Deployment\Models\DeploymentTarget;

class DeploymentTargetPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DeploymentTarget $target): bool
    {
        return true; // tenant scope already ensures org match
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['CEO', 'Ops', 'System_Admin'])
            || $user->roles->contains(fn ($r) => str_ends_with($r->name, '_pm'));
    }

    public function update(User $user, DeploymentTarget $target): bool
    {
        return $user->hasAnyRole(['CEO', 'Ops', 'System_Admin'])
            || $user->hasRole($target->vertical_code . '_pm');
    }

    public function advance(User $user, DeploymentTarget $target): bool
    {
        return $user->hasAnyRole(['CEO', 'System_Admin'])
            || $user->hasRole($target->vertical_code . '_pm');
    }
}

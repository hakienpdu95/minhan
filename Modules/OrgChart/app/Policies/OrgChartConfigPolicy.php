<?php

namespace Modules\OrgChart\Policies;

use App\Models\User;
use Modules\OrgChart\Models\OrgChartConfig;

class OrgChartConfigPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'ceo', 'hr', 'ops', 'sales', 'marketing', 'viewer']);
    }

    public function view(User $user, OrgChartConfig $config): bool
    {
        return $user->hasAnyRole(['system_admin', 'ceo', 'hr', 'ops', 'sales', 'marketing', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['system_admin', 'ceo', 'hr', 'ops']);
    }

    public function update(User $user, OrgChartConfig $config): bool
    {
        return $user->hasAnyRole(['system_admin', 'ceo', 'hr', 'ops']);
    }

    public function delete(User $user, OrgChartConfig $config): bool
    {
        return $user->hasAnyRole(['system_admin', 'ceo']);
    }
}

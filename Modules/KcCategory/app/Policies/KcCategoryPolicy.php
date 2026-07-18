<?php

namespace Modules\KcCategory\Policies;

use App\Models\User;
use Modules\KcCategory\Models\KcCategory;

class KcCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ceo', 'ops', 'hr', 'ai_operator', 'sales', 'marketing', 'viewer']);
    }

    public function view(User $user, KcCategory $kcCategory): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ceo', 'ops', 'hr', 'ai_operator', 'sales', 'marketing', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ops', 'ai_operator']);
    }

    public function update(User $user, KcCategory $kcCategory): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ops', 'ai_operator']);
    }

    public function delete(User $user, KcCategory $kcCategory): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin']);
    }
}

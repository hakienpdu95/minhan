<?php

namespace Modules\KcItem\Policies;

use App\Models\User;
use Modules\KcItem\Models\KcTag;

class KcTagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ceo', 'ops', 'hr', 'ai_operator', 'sales', 'marketing', 'viewer']);
    }

    public function view(User $user, KcTag $kcTag): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ceo', 'ops', 'hr', 'ai_operator', 'sales', 'marketing', 'viewer']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ops', 'ai_operator', 'hr', 'marketing']);
    }

    public function update(User $user, KcTag $kcTag): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ops', 'ai_operator', 'hr', 'marketing']);
    }

    public function delete(User $user, KcTag $kcTag): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ops', 'ai_operator']);
    }
}

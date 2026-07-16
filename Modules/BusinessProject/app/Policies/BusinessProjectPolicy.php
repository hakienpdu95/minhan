<?php

namespace Modules\BusinessProject\Policies;

use App\Enums\PermissionEnum as P;
use App\Models\User;
use Modules\BusinessProject\Models\BusinessProject;

class BusinessProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(P::BUSINESS_PROJECT_VIEW->value);
    }

    public function view(User $user, BusinessProject $businessProject): bool
    {
        if (! $user->can(P::BUSINESS_PROJECT_VIEW->value)) {
            return false;
        }

        // Founder/Admin (permission quản lý toàn cục) xem mọi project; các role
        // project-scoped khác (consultant/ba/pm...) chỉ xem project mình tham gia.
        if ($user->hasAnyRole(['ceo', 'system_admin', 'lead_consultant'])) {
            return true;
        }

        return $businessProject->isMember($user);
    }

    public function create(User $user): bool
    {
        return $user->can(P::BUSINESS_PROJECT_CREATE->value);
    }

    public function update(User $user, BusinessProject $businessProject): bool
    {
        if (! $user->can(P::BUSINESS_CONTEXT_MANAGE->value) && ! $user->can(P::BUSINESS_PROJECT_MANAGE->value)) {
            return false;
        }

        if ($user->hasAnyRole(['ceo', 'system_admin'])) {
            return true;
        }

        return $businessProject->isMember($user);
    }
}

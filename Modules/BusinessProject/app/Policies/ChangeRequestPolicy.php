<?php

namespace Modules\BusinessProject\Policies;

use App\Enums\PermissionEnum as P;
use App\Models\User;
use Modules\BusinessProject\Models\ChangeRequest;

class ChangeRequestPolicy
{
    public function view(User $user, ChangeRequest $changeRequest): bool
    {
        if (! $user->can(P::BUSINESS_PROJECT_VIEW->value)) {
            return false;
        }

        if ($user->hasAnyRole(['ceo', 'system_admin', 'lead_consultant'])) {
            return true;
        }

        return $changeRequest->businessProject->isMember($user);
    }

    public function manage(User $user, ChangeRequest $changeRequest): bool
    {
        if (! $user->can(P::BUSINESS_DELIVERY_MANAGE->value)) {
            return false;
        }

        if ($user->hasAnyRole(['ceo', 'system_admin'])) {
            return true;
        }

        return $changeRequest->businessProject->isMember($user);
    }

    /**
     * Cùng nguyên tắc DeliverablePolicy::approve() — Founder bypass qua business_context.approve
     * (đóng vai trò "CEO override approve" chung cho mọi Approval Service trong BCOS, không tách
     * permission riêng theo từng loại approvable), còn lại dùng Ringlesoft canBeApprovedBy().
     */
    public function approve(User $user, ChangeRequest $changeRequest): bool
    {
        if ($user->hasRole('ceo') && $user->can(P::BUSINESS_CONTEXT_APPROVE->value)) {
            return true;
        }

        return (bool) $changeRequest->canBeApprovedBy($user);
    }
}

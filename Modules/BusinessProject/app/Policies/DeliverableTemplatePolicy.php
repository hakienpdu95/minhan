<?php

namespace Modules\BusinessProject\Policies;

use App\Enums\PermissionEnum as P;
use App\Models\User;
use Modules\BusinessProject\Models\DeliverableTemplate;

/**
 * Template Library (Phase 2, mảng 5/5) — KHÔNG gắn với 1 Business Project cụ thể (template
 * dùng chung/tổ chức), nên không theo pattern `manageWorkspace()` của `BusinessProjectPolicy`
 * (không cần check `isMember()`), chỉ cần đúng permission toàn cục.
 */
class DeliverableTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(P::BUSINESS_TEMPLATE_MANAGE->value);
    }

    public function create(User $user): bool
    {
        return $user->can(P::BUSINESS_TEMPLATE_MANAGE->value);
    }

    public function update(User $user, DeliverableTemplate $template): bool
    {
        return $user->can(P::BUSINESS_TEMPLATE_MANAGE->value);
    }

    public function delete(User $user, DeliverableTemplate $template): bool
    {
        return $user->can(P::BUSINESS_TEMPLATE_MANAGE->value);
    }
}

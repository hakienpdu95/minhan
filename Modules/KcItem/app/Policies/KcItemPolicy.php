<?php

namespace Modules\KcItem\Policies;

use App\Models\User;
use Modules\KcItem\Models\KcItem;

class KcItemPolicy
{
    /**
     * BCOS (Business Consulting OS) — `lead_consultant`/`consultant`/`pm` thêm vào để dùng được
     * link "Tạo Knowledge Asset mới" từ Closing Workspace (Modules\BusinessProject, Rule R7),
     * cùng pattern đã áp dụng cho `Modules\Task\Policies\TaskPolicy`.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ceo', 'ops', 'hr', 'ai_operator', 'sales', 'marketing', 'viewer', 'lead_consultant', 'consultant', 'pm']);
    }

    public function view(User $user, KcItem $kcItem): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ceo', 'ops', 'hr', 'ai_operator', 'sales', 'marketing', 'viewer', 'lead_consultant', 'consultant', 'pm']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ops', 'ai_operator', 'hr', 'marketing', 'ceo', 'lead_consultant', 'consultant', 'pm']);
    }

    public function update(User $user, KcItem $kcItem): bool
    {
        if ($user->hasAnyRole(['super-admin', 'system_admin'])) {
            return true;
        }

        if ($user->hasAnyRole(['ops', 'ai_operator', 'hr', 'marketing'])) {
            return $kcItem->isEditable();
        }

        return false;
    }

    public function delete(User $user, KcItem $kcItem): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin']);
    }

    public function submit(User $user, KcItem $kcItem): bool
    {
        return $kcItem->canSubmit()
            && $user->hasAnyRole(['super-admin', 'system_admin', 'ops', 'ai_operator', 'hr', 'marketing']);
    }

    public function approve(User $user, KcItem $kcItem): bool
    {
        if (! $kcItem->canApprove()) {
            return false;
        }

        if (! $user->hasAnyRole(['super-admin', 'system_admin', 'ops', 'ai_operator'])) {
            return false;
        }

        // Tác giả không được tự duyệt (trừ admin)
        if ($user->id === $kcItem->owner_id && ! $user->hasAnyRole(['super-admin', 'system_admin'])) {
            return false;
        }

        return true;
    }

    public function reject(User $user, KcItem $kcItem): bool
    {
        return $this->approve($user, $kcItem);
    }

    public function archive(User $user, KcItem $kcItem): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ops', 'ai_operator']);
    }

    public function rollback(User $user, KcItem $kcItem): bool
    {
        if ($user->hasAnyRole(['super-admin', 'system_admin'])) {
            return true;
        }

        if ($user->id === $kcItem->owner_id) {
            return true;
        }

        return $user->hasRole('ops');
    }
}

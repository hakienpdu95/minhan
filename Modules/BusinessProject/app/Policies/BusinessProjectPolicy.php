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

    /**
     * Fallback dùng bởi `authorizeResource()` (route PUT/PATCH resource chung — hiện chưa có
     * route nào gọi thẳng ability này). Các Controller ghi dữ liệu theo từng workspace PHẢI
     * dùng ability riêng (manageContext/manageDiscovery/manageTransformation...), KHÔNG dùng
     * update() — từ khi Transformation thêm role PM (chỉ có business_transformation.manage,
     * KHÔNG có business_context.manage/business_discovery.manage), một ability chung sẽ để PM
     * lọt qua authorize('update', ...) ở BusinessContextController/DiscoveryController dù matrix
     * Phần 7.2 không cho phép PM thao tác 2 workspace đó.
     */
    public function update(User $user, BusinessProject $businessProject): bool
    {
        return $this->manageAnyWorkspace($user, $businessProject);
    }

    public function manageContext(User $user, BusinessProject $businessProject): bool
    {
        return $this->manageWorkspace($user, $businessProject, P::BUSINESS_CONTEXT_MANAGE->value);
    }

    public function manageDiscovery(User $user, BusinessProject $businessProject): bool
    {
        return $this->manageWorkspace($user, $businessProject, P::BUSINESS_DISCOVERY_MANAGE->value);
    }

    public function manageDiagnosis(User $user, BusinessProject $businessProject): bool
    {
        return $this->manageWorkspace($user, $businessProject, P::BUSINESS_DIAGNOSIS_MANAGE->value);
    }

    public function manageTransformation(User $user, BusinessProject $businessProject): bool
    {
        return $this->manageWorkspace($user, $businessProject, P::BUSINESS_TRANSFORMATION_MANAGE->value);
    }

    public function manageDelivery(User $user, BusinessProject $businessProject): bool
    {
        return $this->manageWorkspace($user, $businessProject, P::BUSINESS_DELIVERY_MANAGE->value);
    }

    public function manageClosing(User $user, BusinessProject $businessProject): bool
    {
        return $this->manageWorkspace($user, $businessProject, P::BUSINESS_CLOSING_MANAGE->value);
    }

    public function manageKnowledge(User $user, BusinessProject $businessProject): bool
    {
        return $this->manageWorkspace($user, $businessProject, P::BUSINESS_KNOWLEDGE_MANAGE->value);
    }

    public function manageCustomerSuccess(User $user, BusinessProject $businessProject): bool
    {
        return $this->manageWorkspace($user, $businessProject, P::BUSINESS_CUSTOMER_SUCCESS_MANAGE->value);
    }

    private function manageWorkspace(User $user, BusinessProject $businessProject, string $permission): bool
    {
        if (! $user->can($permission) && ! $user->can(P::BUSINESS_PROJECT_MANAGE->value)) {
            return false;
        }

        if ($user->hasAnyRole(['ceo', 'system_admin'])) {
            return true;
        }

        return $businessProject->isMember($user);
    }

    private function manageAnyWorkspace(User $user, BusinessProject $businessProject): bool
    {
        $canManageAnyWorkspace = $user->can(P::BUSINESS_CONTEXT_MANAGE->value)
            || $user->can(P::BUSINESS_DIAGNOSIS_MANAGE->value)
            || $user->can(P::BUSINESS_DISCOVERY_MANAGE->value)
            || $user->can(P::BUSINESS_TRANSFORMATION_MANAGE->value)
            || $user->can(P::BUSINESS_DELIVERY_MANAGE->value)
            || $user->can(P::BUSINESS_CLOSING_MANAGE->value)
            || $user->can(P::BUSINESS_KNOWLEDGE_MANAGE->value)
            || $user->can(P::BUSINESS_CUSTOMER_SUCCESS_MANAGE->value)
            || $user->can(P::BUSINESS_PROJECT_MANAGE->value);

        if (! $canManageAnyWorkspace) {
            return false;
        }

        if ($user->hasAnyRole(['ceo', 'system_admin'])) {
            return true;
        }

        return $businessProject->isMember($user);
    }
}

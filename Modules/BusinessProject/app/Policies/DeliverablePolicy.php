<?php

namespace Modules\BusinessProject\Policies;

use App\Enums\PermissionEnum as P;
use App\Models\User;
use Modules\BusinessProject\Enums\BusinessProjectStage;
use Modules\BusinessProject\Models\Deliverable;

class DeliverablePolicy
{
    public function view(User $user, Deliverable $deliverable): bool
    {
        if (! $user->can(P::BUSINESS_PROJECT_VIEW->value)) {
            return false;
        }

        if ($user->hasAnyRole(['ceo', 'system_admin', 'lead_consultant'])) {
            return true;
        }

        return $deliverable->businessProject->isMember($user);
    }

    /**
     * Permission cần thiết phụ thuộc workspace của deliverable — mỗi Workspace có permission
     * quản lý riêng (Phần 7.1: 2 lớp phân quyền), ability chung "manage" chỉ dispatch theo type,
     * không tự chế cột permission mới cho từng loại deliverable.
     */
    public function manage(User $user, Deliverable $deliverable): bool
    {
        $permission = match ($deliverable->workspace) {
            BusinessProjectStage::Context => P::BUSINESS_CONTEXT_MANAGE->value,
            BusinessProjectStage::Diagnosis => P::BUSINESS_DIAGNOSIS_MANAGE->value,
            BusinessProjectStage::Discovery => P::BUSINESS_DISCOVERY_MANAGE->value,
            BusinessProjectStage::Transformation => P::BUSINESS_TRANSFORMATION_MANAGE->value,
            BusinessProjectStage::Delivery => P::BUSINESS_DELIVERY_MANAGE->value,
            BusinessProjectStage::Closing => P::BUSINESS_CLOSING_MANAGE->value,
            default => P::BUSINESS_PROJECT_MANAGE->value,
        };

        if (! $user->can($permission)) {
            return false;
        }

        if ($user->hasAnyRole(['ceo', 'system_admin'])) {
            return true;
        }

        return $deliverable->businessProject->isMember($user);
    }

    /**
     * Rule R1/R3 — ai được duyệt Deliverable. Founder luôn được duyệt (đúng ma trận
     * vai trò Phần 7.2: Founder ✅ ở mọi hàng approval). Ngoài ra dùng thẳng
     * canBeApprovedBy() của Ringlesoft (role bước duyệt hiện tại = lead_consultant).
     *
     * Hạn chế biết trước ở Vertical Slice 1 (đã ghi trong plan): bất kỳ ai có role
     * global lead_consultant đều duyệt được Context của MỌI project, chưa siết theo
     * business_project_members. Phase 2 sẽ thêm điều kiện isMember() khi có nhiều
     * Lead Consultant chạy song song nhiều project.
     */
    public function approve(User $user, Deliverable $deliverable): bool
    {
        if ($user->hasRole('ceo') && $user->can(P::BUSINESS_CONTEXT_APPROVE->value)) {
            return true;
        }

        return (bool) $deliverable->canBeApprovedBy($user);
    }
}

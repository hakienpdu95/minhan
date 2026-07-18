<?php

namespace Modules\Task\Policies;

use App\Models\User;
use Modules\Task\Models\Task;

/**
 * Bug đã fix: role name trước đây viết hoa ('CEO', 'System_Admin'...) không khớp role thật được
 * seed viết thường (`database/seeders/RolePermissionSeeder.php` — Spatie `hasAnyRole()` so khớp
 * chuỗi chính xác) — khóa toàn bộ module Task cho MỌI user, không riêng BCOS. Đã sửa về đúng
 * lowercase + thêm 3 role BCOS (lead_consultant/consultant/pm) vào view/viewAny/create để nút
 * "Tạo Task mới" ở Delivery Workspace (Modules\BusinessProject) dùng được cho vai trò BCOS.
 */
class TaskPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ceo', 'hr', 'ops', 'sales', 'marketing', 'viewer', 'ai_operator', 'lead_consultant', 'consultant', 'pm']);
    }

    public function view(User $user, Task $task): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ceo', 'hr', 'ops', 'sales', 'marketing', 'viewer', 'ai_operator', 'lead_consultant', 'consultant', 'pm']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ceo', 'hr', 'ops', 'lead_consultant', 'consultant', 'pm']);
    }

    public function update(User $user, Task $task): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ceo', 'hr', 'ops', 'lead_consultant', 'consultant', 'pm']);
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->hasAnyRole(['super-admin', 'system_admin', 'ceo', 'ops']);
    }
}

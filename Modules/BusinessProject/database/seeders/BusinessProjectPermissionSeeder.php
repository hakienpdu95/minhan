<?php

namespace Modules\BusinessProject\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\BusinessProject\Models\Deliverable;
use RingleSoft\LaravelProcessApproval\Models\ProcessApprovalFlow;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class BusinessProjectPermissionSeeder extends Seeder
{
    private const PERMISSIONS = [
        'business_project.view',
        'business_project.create',
        'business_project.manage',
        'business_context.manage',
        'business_context.approve',
    ];

    private const ROLES = [
        'lead_consultant',
        'consultant',
        'ba',
        'pm',
        'customer_success',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        // Roles + mapping thật đã được RolePermissionSeeder (chạy trước, xem
        // database/seeders/SystemDataSeeder.php) tạo và gán đầy đủ. Seeder này chỉ
        // đảm bảo idempotent nếu bị gọi độc lập (firstOrCreate không tạo trùng).
        foreach (self::ROLES as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->seedApprovalFlow();

        $this->command?->info('  ✓ BusinessProject permissions + roles + approval flow seeded.');
    }

    /**
     * Đấu nối lần đầu vendor ringlesoft/laravel-process-approval — Approval Service
     * dùng chung cho MỌI loại Deliverable ở MỌI workspace (Phần 8.1 spec). Idempotent:
     * makeApprovable() ném ApprovalFlowExistsException nếu flow cho Deliverable::class
     * đã tồn tại, nên phải check trước.
     */
    private function seedApprovalFlow(): void
    {
        $exists = ProcessApprovalFlow::where('approvable_type', Deliverable::class)->exists();

        if (! $exists) {
            Deliverable::makeApprovable(['lead_consultant' => 'APPROVE'], 'Deliverable Approval');
        }
    }
}

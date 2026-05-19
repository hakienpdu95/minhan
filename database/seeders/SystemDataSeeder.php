<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Database\Seeders\AuthDatabaseSeeder;
use Modules\Organization\Database\Seeders\OrganizationRolePermissionSeeder;

/**
 * Master Seeder — điểm khởi chạy duy nhất cho toàn bộ dữ liệu mặc định hệ thống.
 *
 * Thứ tự bắt buộc (dependency order):
 *  1. RolePermissionSeeder              — IAM: 8 tenant roles + 40+ permissions
 *  2. AuthDatabaseSeeder                — super-admin role + 2 tài khoản hệ thống
 *  3. OrganizationRolePermissionSeeder  — template roles cấp org (owner/admin/manager/member)
 *  4. OrganizationSeeder                — demo organization
 *  5. UserSeeder                        — 8 test users (1 per role)
 *
 * Khi thêm module mới có data seed → tạo seeder riêng và đăng ký ở mục 6+.
 *
 * Lệnh chạy:
 *   php artisan system:seed
 *   php artisan db:seed --class=Database\\Seeders\\SystemDataSeeder
 */
class SystemDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->newLine();
        $this->command->info('┌──────────────────────────────────────────┐');
        $this->command->info('│       SystemDataSeeder — starting...     │');
        $this->command->info('└──────────────────────────────────────────┘');
        $this->command->newLine();

        $this->call([
            // ── 1. IAM: roles + permissions ──────────────────────────────
            RolePermissionSeeder::class,

            // ── 2. System admin accounts ──────────────────────────────────
            AuthDatabaseSeeder::class,

            // ── 3. Organization template roles ────────────────────────────
            OrganizationRolePermissionSeeder::class,

            // ── 4. Demo organization ──────────────────────────────────────
            OrganizationSeeder::class,

            // ── 5. Test users (1 per role) ───────────────────────────────
            UserSeeder::class,

            // ── 6. Module seeders — đăng ký thêm tại đây theo dependency ─
            // ExampleModuleSeeder::class,
        ]);

        $this->command->newLine();
        $this->command->info('  ✓ Tất cả dữ liệu mặc định đã được seed thành công.');
        $this->command->newLine();
    }
}

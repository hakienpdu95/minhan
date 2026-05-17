<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Database\Seeders\AuthDatabaseSeeder;
use Modules\Organization\Database\Seeders\OrganizationRolePermissionSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,              // 1. Permissions + 8 tenant roles
            AuthDatabaseSeeder::class,                // 2. super-admin role + 2 system accounts
            OrganizationRolePermissionSeeder::class,  // 3. Org template roles (owner/admin/manager/member)
            OrganizationSeeder::class,                // 4. Demo organization
            UserSeeder::class,                        // 5. Demo users
        ]);
    }
}

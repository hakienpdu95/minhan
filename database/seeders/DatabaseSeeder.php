<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Database\Seeders\AuthDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,   // 1. Roles + permissions (8 tenant roles)
            AuthDatabaseSeeder::class,     // 2. super-admin role + 2 system accounts
            OrganizationSeeder::class,     // 3. Demo organization
            UserSeeder::class,             // 4. Demo users (8 roles × 1 user)
        ]);
    }
}

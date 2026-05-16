<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,  // 1. Tạo roles + permissions trước
            OrganizationSeeder::class,    // 2. Tạo demo org
            UserSeeder::class,            // 3. Tạo users + gán roles
        ]);
    }
}

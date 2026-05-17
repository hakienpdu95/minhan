<?php

namespace Modules\Organization\Database\Seeders;

use Database\Seeders\OrganizationSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Database\Seeder;

/**
 * Main seeder for the Organization module.
 *
 * Runs in order:
 *  1. OrganizationRolePermissionSeeder — create template org roles
 *  2. OrganizationSeeder (app-level)   — seed the demo organization
 *  3. UserSeeder (app-level)           — seed demo users with roles
 */
class OrganizationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            OrganizationRolePermissionSeeder::class,
            OrganizationSeeder::class,
            UserSeeder::class,
        ]);
    }
}

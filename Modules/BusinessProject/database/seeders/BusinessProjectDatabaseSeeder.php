<?php

namespace Modules\BusinessProject\Database\Seeders;

use Illuminate\Database\Seeder;

class BusinessProjectDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            BusinessProjectPermissionSeeder::class,
            BcosAutomationSeeder::class,
        ]);
    }
}

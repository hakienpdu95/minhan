<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill model_has_roles.organization_id for users whose pivot entry was
 * created before setPermissionsTeamId() was called consistently.
 *
 * Pattern:
 *   - super-admin / system accounts: user.organization_id IS NULL → keep NULL in pivot (correct)
 *   - regular users: user.organization_id IS NOT NULL → pivot must match (was incorrectly NULL)
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE model_has_roles mhr
            INNER JOIN users u
                ON u.id  = mhr.model_id
               AND mhr.model_type = 'App\\\\Models\\\\User'
            SET mhr.organization_id = u.organization_id
            WHERE mhr.organization_id IS NULL
              AND u.organization_id  IS NOT NULL
        ");
    }

    public function down(): void
    {
        // Intentionally not reversible — setting NULL on all rows would
        // be destructive for data created after this migration ran.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill trust_level=1 for users who already verified their email
        DB::table('users')
            ->whereNotNull('email_verified_at')
            ->where('trust_level', 0)
            ->update(['trust_level' => 1]);

        // Backfill account_type for users who belong to an org
        // Users linked to an active organization_members row → org_member
        $memberUserIds = DB::table('organization_members')
            ->where('status', 'active')
            ->pluck('user_id')
            ->toArray();

        if (!empty($memberUserIds)) {
            DB::table('users')
                ->whereIn('id', $memberUserIds)
                ->where('account_type', 'free')
                ->update(['account_type' => 'org_member']);
        }
    }

    public function down(): void
    {
        // Non-reversible data migration — no rollback
    }
};

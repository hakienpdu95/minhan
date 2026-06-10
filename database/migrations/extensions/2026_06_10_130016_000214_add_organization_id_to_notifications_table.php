<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->after('uuid');

            // Multi-tenant query: filter by org + user + read status
            $table->index(['organization_id', 'notifiable_id', 'read_at'], 'notif_org_user_read_idx');
            // Pagination ORDER BY created_at DESC
            $table->index('created_at', 'notif_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('notif_org_user_read_idx');
            $table->dropIndex('notif_created_at_idx');
            $table->dropColumn('organization_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->index('province_code', 'idx_orgs_province');
            $table->index('ward_code',     'idx_orgs_ward');
            $table->index('status',        'idx_orgs_status');
            $table->index('created_at',    'idx_orgs_created_at');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropIndex('idx_orgs_province');
            $table->dropIndex('idx_orgs_ward');
            $table->dropIndex('idx_orgs_status');
            $table->dropIndex('idx_orgs_created_at');
        });
    }
};

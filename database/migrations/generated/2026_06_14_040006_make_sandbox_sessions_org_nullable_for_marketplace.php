<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marketplace candidates (free users) không thuộc về org nào.
 * Cần cho phép sandbox_sessions.organization_id và workforce_profile_id là NULL
 * để campaign participants có thể tạo session mà không cần org context.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sandbox_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->change();
            $table->unsignedBigInteger('workforce_profile_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sandbox_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
            $table->unsignedBigInteger('workforce_profile_id')->nullable(false)->change();
        });
    }
};

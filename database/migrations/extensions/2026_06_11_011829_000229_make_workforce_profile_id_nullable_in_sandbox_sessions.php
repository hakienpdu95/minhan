<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sandbox_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('workforce_profile_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sandbox_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('workforce_profile_id')->nullable(false)->change();
        });
    }
};

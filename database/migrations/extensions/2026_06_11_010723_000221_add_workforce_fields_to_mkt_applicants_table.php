<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mkt_applicants', function (Blueprint $table) {
            $table->unsignedBigInteger('workforce_profile_id')->nullable();
            $table->decimal('ai_readiness_score', 5, 2)->nullable();
            $table->string('highest_cert_level', 30)->nullable();
            $table->string('career_goal', 200)->nullable();
            $table->index('workforce_profile_id', 'idx_mktapp_wfp');
        });
    }

    public function down(): void
    {
        Schema::table('mkt_applicants', function (Blueprint $table) {
            $table->dropIndex('idx_mktapp_wfp');
            $table->dropColumn([
                'workforce_profile_id',
                'ai_readiness_score',
                'highest_cert_level',
                'career_goal',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforce_recommendations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('workforce_profile_id');
            $table->timestamp('generated_at')->nullable();
            $table->string('provider', 30)->default('claude');
            $table->string('model', 60)->nullable();
            $table->char('context_hash', 32)->nullable();
            $table->json('recommendations')->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->tinyInteger('is_stale')->default(0);
            $table->timestamps();

            $table->index(['organization_id', 'workforce_profile_id'], 'wr_org_profile_idx');
            $table->index('context_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workforce_recommendations');
    }
};

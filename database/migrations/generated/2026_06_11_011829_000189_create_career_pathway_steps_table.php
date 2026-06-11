<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_pathway_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->comment('null = global template');
            $table->string('from_level', 64)->nullable()->comment('DIGITAL_BEGINNER|DIGITAL_AWARE|...');
            $table->string('to_level', 64)->comment('Level cần đạt');
            $table->unsignedTinyInteger('step_order');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('required_cert_code', 50)->nullable()->comment('Chứng nhận cần đạt');
            $table->string('recommended_kc_tag', 100)->nullable()->comment('Tag KcItem gợi ý học');
            $table->string('recommended_sandbox_env_code', 50)->nullable()->comment('Sandbox nên thực hành');
            $table->unsignedSmallInteger('estimated_weeks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['from_level', 'to_level'], 'idx_cps_from_to');
            $table->index('organization_id', 'idx_cps_org');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('career_pathway_steps');
    }
};

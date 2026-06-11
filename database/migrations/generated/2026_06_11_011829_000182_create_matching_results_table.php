<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matching_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('workforce_profile_id');
            $table->unsignedBigInteger('mkt_listing_id');
            $table->unsignedBigInteger('mkt_applicant_id')->nullable();

            // Điểm thành phần theo công thức tài liệu gốc
            // Matching Score = Năng lực×40% + Chứng nhận×20% + Kinh nghiệm×15% + AI Readiness×15% + Career Goal×10%
            $table->decimal('competency_match', 5, 2)->nullable()->comment('40%');
            $table->decimal('certification_match', 5, 2)->nullable()->comment('20%');
            $table->decimal('experience_match', 5, 2)->nullable()->comment('15%');
            $table->decimal('ai_readiness_match', 5, 2)->nullable()->comment('15%');
            $table->decimal('career_goal_match', 5, 2)->nullable()->comment('10%');
            $table->decimal('matching_score', 5, 2)->nullable()->comment('Điểm tổng hợp');

            // Phân loại mức phù hợp
            $table->string('match_level', 20)->nullable()
                ->comment('excellent(90-100)|strong(75-89)|potential(60-74)|development(40-59)|not_recommended(<40)');

            $table->timestamp('calculated_at');
            $table->string('status', 20)->default('pending')->comment('pending|reviewed|hired|rejected');
            $table->timestamps();

            $table->index(['workforce_profile_id', 'mkt_listing_id'], 'idx_mr_profile_listing');
            $table->index('matching_score', 'idx_mr_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matching_results');
    }
};

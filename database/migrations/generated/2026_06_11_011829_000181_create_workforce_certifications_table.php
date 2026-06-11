<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforce_certifications', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique();
            $table->unsignedBigInteger('organization_id');
            $table->unsignedBigInteger('workforce_profile_id');
            $table->unsignedBigInteger('cert_definition_id');

            // Composite Score snapshot tại thời điểm cấp
            // Formula: Assessment×30% + Sandbox×25% + Impact×25% + Portfolio×20%
            $table->decimal('assessment_score_at_issue', 5, 2)->nullable()->comment('30%');
            $table->decimal('sandbox_score_at_issue', 5, 2)->nullable()->comment('25%');
            $table->decimal('impact_score_at_issue', 5, 2)->nullable()->comment('25%');
            $table->decimal('portfolio_score_at_issue', 5, 2)->nullable()->comment('20%');
            $table->decimal('composite_score_at_issue', 5, 2)->nullable()->comment('Điểm tổng hợp cuối cùng');

            $table->string('status', 20)->default('active')->comment('active|expired|revoked');
            $table->timestamp('issued_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->text('revoked_reason')->nullable();
            $table->string('certificate_number', 50)->unique()->nullable();
            $table->string('qr_code_url', 500)->nullable();
            $table->string('digital_badge_url', 500)->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->unsignedBigInteger('human_reviewer_id')->nullable()->comment('Chuyên gia Human-in-the-Loop');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('workforce_profile_id')->references('id')->on('workforce_profiles')->cascadeOnDelete();
            $table->foreign('cert_definition_id')->references('id')->on('certification_definitions');
            $table->index(['workforce_profile_id', 'status'], 'idx_wfcert_profile_status');
            $table->index('expires_at', 'idx_wfcert_expires');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workforce_certifications');
    }
};

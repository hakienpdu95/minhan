<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployment_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('vertical_code', 50);

            // Org ĐƯỢC triển khai đến — luôn là 1 Organization record
            $table->foreignId('target_organization_id')->constrained('organizations')->restrictOnDelete();

            $table->string('current_phase', 50)->default('draft');
            $table->foreignId('assigned_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['project_id', 'target_organization_id']);
            $table->index(['organization_id', 'vertical_code']);
            $table->index('current_phase');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_targets');
    }
};

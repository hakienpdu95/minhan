<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Spec Phần 6.1: "risks — likelihood/impact; issue & risk có thể escalate -> change_requests".
        Schema::create('risks', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('likelihood', ['low', 'medium', 'high'])->default('medium')->index();
            $table->enum('impact', ['low', 'medium', 'high'])->default('medium')->index();
            $table->enum('status', ['open', 'mitigated', 'escalated'])->default('open')->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risks');
    }
};

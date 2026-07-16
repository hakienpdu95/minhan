<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Spec Phần 6.1: "change_requests — liên kết issue/risk nguồn; duyệt qua Approval Service".
        // source_type + đúng 1 trong 2 FK (issue_id/risk_id) được set tùy nguồn — validate ở tầng
        // Action, không ép DB constraint (Ringlesoft ApprovableModel không thuộc Deliverable nên
        // cần flow phê duyệt RIÊNG "Change Request Approval", đăng ký ở BusinessProjectPermissionSeeder).
        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->enum('source_type', ['issue', 'risk'])->index();
            $table->foreignId('issue_id')->nullable()->constrained('issues')->nullOnDelete();
            $table->foreignId('risk_id')->nullable()->constrained('risks')->nullOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->boolean('impacts_scope')->default(false);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft')->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('change_requests');
    }
};

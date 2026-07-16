<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_contexts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->json('company_profile')->nullable();
            $table->json('stakeholders')->nullable();
            $table->json('strategic_goals')->nullable();
            $table->foreignId('deliverable_id')->nullable()->constrained('deliverables')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            // TenantAwareModel bundles SoftDeletes — mọi model extends nó cần cột này.
            $table->softDeletes();

            // Rule R1 — 1 Business Project chỉ có 1 Business Context. Lưới an toàn DB;
            // CreateBusinessContextAction chặn thêm ở tầng Service trước khi chạm tới đây.
            $table->unique('business_project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_contexts');
    }
};

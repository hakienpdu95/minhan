<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Spec Phần 6.3 + Rule R7: kc_items + business_project_id (nullable — KcItem ngoài BCOS
        // vẫn hợp lệ). Liên kết 2 chiều đầy đủ (industry, types case_study/lessons_learned...)
        // là việc Phase 2 (Knowledge Workspace) — ở đây chỉ đủ tối thiểu cho Gate R7 đếm được
        // "≥1 Knowledge Asset gắn project". Cùng pattern add_business_project_id_to_tasks_table.
        Schema::table('kc_items', function (Blueprint $table) {
            $table->unsignedBigInteger('business_project_id')->nullable()->after('category_id');
            $table->index(['organization_id', 'business_project_id'], 'idx_kcitem_business_project');
            $table->foreign('business_project_id')
                ->references('id')->on('business_projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kc_items', function (Blueprint $table) {
            $table->dropForeign(['business_project_id']);
            $table->dropIndex('idx_kcitem_business_project');
            $table->dropColumn('business_project_id');
        });
    }
};

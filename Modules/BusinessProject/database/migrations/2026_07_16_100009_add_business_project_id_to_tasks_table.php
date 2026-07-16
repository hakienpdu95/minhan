<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Spec Phần 6.3: tasks + business_project_id (nullable — task ngoài project vẫn hợp lệ
        // cho nghiệp vụ khác). Cùng pattern với add_converted_business_project_id_to_leads_table.
        Schema::table('tasks', function (Blueprint $table) {
            $table->unsignedBigInteger('business_project_id')->nullable()->after('project_id');
            $table->index(['organization_id', 'business_project_id'], 'idx_task_business_project');
            $table->foreign('business_project_id')
                ->references('id')->on('business_projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['business_project_id']);
            $table->dropIndex('idx_task_business_project');
            $table->dropColumn('business_project_id');
        });
    }
};

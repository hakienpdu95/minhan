<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedBigInteger('converted_business_project_id')->nullable()->after('customer_id');
            $table->index(['organization_id', 'converted_business_project_id'], 'idx_lead_business_project');
            $table->foreign('converted_business_project_id')
                ->references('id')->on('business_projects')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['converted_business_project_id']);
            $table->dropIndex('idx_lead_business_project');
            $table->dropColumn('converted_business_project_id');
        });
    }
};

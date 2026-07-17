<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'business_project_id')) {
                $table->unsignedBigInteger('business_project_id')->nullable();
            }
            if (!Schema::hasIndex('tasks', 'idx_task_business_project')) {
                $table->index(['organization_id', 'business_project_id'], 'idx_task_business_project');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $cols = array_filter(['business_project_id'], fn($c) => Schema::hasColumn('tasks', $c));
            if (!empty($cols)) $table->dropColumn(array_values($cols));
        });
    }
};
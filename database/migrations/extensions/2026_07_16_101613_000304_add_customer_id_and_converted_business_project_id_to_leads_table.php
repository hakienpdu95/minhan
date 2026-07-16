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
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'customer_id')) {
                $table->unsignedBigInteger('customer_id')->nullable();
            }
            if (!Schema::hasIndex('leads', 'idx_lead_customer')) {
                $table->index(['organization_id', 'customer_id'], 'idx_lead_customer');
            }
            if (!Schema::hasColumn('leads', 'converted_business_project_id')) {
                $table->unsignedBigInteger('converted_business_project_id')->nullable()->after('customer_id');
            }
            if (!Schema::hasIndex('leads', 'idx_lead_business_project')) {
                $table->index(['organization_id', 'converted_business_project_id'], 'idx_lead_business_project');
            }
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $cols = array_filter(['customer_id', 'converted_business_project_id'], fn($c) => Schema::hasColumn('leads', $c));
            if (!empty($cols)) $table->dropColumn(array_values($cols));
        });
    }
};
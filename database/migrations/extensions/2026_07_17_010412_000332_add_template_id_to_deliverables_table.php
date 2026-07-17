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
        Schema::table('deliverables', function (Blueprint $table) {
            if (!Schema::hasColumn('deliverables', 'template_id')) {
                $table->foreignId('template_id')->nullable()->constrained('deliverable_templates')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('deliverables', function (Blueprint $table) {
            if (Schema::hasColumn('deliverables', 'template_id')) $table->dropForeign(['template_id']);
            $cols = array_filter(['template_id'], fn($c) => Schema::hasColumn('deliverables', $c));
            if (!empty($cols)) $table->dropColumn(array_values($cols));
        });
    }
};
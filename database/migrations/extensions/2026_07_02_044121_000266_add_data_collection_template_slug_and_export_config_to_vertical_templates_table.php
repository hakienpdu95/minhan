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
        Schema::table('vertical_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('vertical_templates', 'data_collection_template_slug')) {
                $table->string('data_collection_template_slug', 100)->nullable()->comment('slug survey template thu thập dữ liệu thực địa — ví dụ: data_collection_v1');
            }
            if (!Schema::hasColumn('vertical_templates', 'export_config')) {
                $table->json('export_config')->nullable()->after('data_collection_template_slug')->comment('Optional export config JSON (sheets, columns, source mappings) for future vertical-specific export formats');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vertical_templates', function (Blueprint $table) {
            $cols = array_filter(['data_collection_template_slug', 'export_config'], fn($c) => Schema::hasColumn('vertical_templates', $c));
            if (!empty($cols)) $table->dropColumn(array_values($cols));
        });
    }
};
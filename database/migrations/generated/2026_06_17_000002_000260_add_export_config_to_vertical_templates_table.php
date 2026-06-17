<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vertical_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('vertical_templates', 'export_config')) {
                $table->json('export_config')
                    ->nullable()
                    ->after('export_adapter')
                    ->comment('Optional export config JSON (sheets, columns, source mappings) for future vertical-specific export formats');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vertical_templates', function (Blueprint $table) {
            if (Schema::hasColumn('vertical_templates', 'export_config')) {
                $table->dropColumn('export_config');
            }
        });
    }
};

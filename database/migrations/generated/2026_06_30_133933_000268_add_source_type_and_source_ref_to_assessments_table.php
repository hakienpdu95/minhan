<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'source_type')) {
                $table->string('source_type', 30)
                      ->default('standalone')
                      ->after('is_active')
                      ->comment('global_template|survey_linked|lead_scoring|standalone');
            }
            if (!Schema::hasColumn('assessments', 'source_ref')) {
                $table->string('source_ref', 255)
                      ->nullable()
                      ->after('source_type')
                      ->comment('slug/code của entity nguồn — VD: survey slug nếu survey_linked');
            }
        });

        // Backfill: global assessments (org_id NULL) → global_template
        DB::table('assessments')->whereNull('organization_id')
            ->update(['source_type' => 'global_template']);
    }

    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $cols = array_filter(['source_type', 'source_ref'], fn($c) => Schema::hasColumn('assessments', $c));
            if (!empty($cols)) {
                $table->dropColumn(array_values($cols));
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('score_rules', function (Blueprint $table) {
            // Behavior scoring columns (Module 120 — T4)
            $table->string('behavior_metric', 30)->nullable()
                ->comment('time_spent | change_count | hesitation_index')
                ->after('section_id');
            $table->decimal('threshold_value', 8, 2)->nullable()
                ->comment('Threshold for comparison')
                ->after('behavior_metric');
            $table->string('operator', 4)->nullable()
                ->comment('< | > | <= | >=')
                ->after('threshold_value');
            $table->integer('score_adjustment')->nullable()
                ->comment('Points to add (positive) or subtract (negative) from domain score')
                ->after('operator');

            // Drop old unique (assessment_code, field_key, domain_code) — does not distinguish
            // regular vs behavior rules on the same field+domain.
            $table->dropUnique('uq_score_rule');

            // New unique includes condition_type so both types can coexist per field+domain.
            $table->unique(
                ['assessment_code', 'field_key', 'domain_code', 'condition_type'],
                'uq_score_rule_v2'
            );
        });
    }

    public function down(): void
    {
        Schema::table('score_rules', function (Blueprint $table) {
            $table->dropUnique('uq_score_rule_v2');
            $table->unique(['assessment_code', 'field_key', 'domain_code'], 'uq_score_rule');
            $table->dropColumn(['behavior_metric', 'threshold_value', 'operator', 'score_adjustment']);
        });
    }
};

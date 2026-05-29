<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2 — Tách Assessment module khỏi Survey.
 *
 * Chiến lược:
 *   1. Tạo assessment_results (polymorphic subject_type + subject_id)
 *   2. Migrate data từ survey_results, giữ nguyên id → child FKs không cần update data
 *   3. Drop FK trên 8 child tables → ADD FK mới sang assessment_results
 *   4. Drop TABLE survey_results
 *   5. CREATE VIEW survey_results (backward compat cho Survey controllers)
 *
 * Rollback: reverse toàn bộ — drop view, restore table, re-point FKs
 */
return new class extends Migration
{
    private const SUBJECT_TYPE_SURVEY = 'Modules\\Survey\\Models\\SurveyResponse';

    private const CHILD_TABLES = [
        'result_domain_scores'   => 'result_domain_scores_result_id_foreign',
        'result_signal_flags'    => 'result_signal_flags_result_id_foreign',
        'result_pain_points'     => 'result_pain_points_result_id_foreign',
        'result_recommendations' => 'result_recommendations_result_id_foreign',
        'result_roadmap_phases'  => 'result_roadmap_phases_result_id_foreign',
        'result_question_scores' => 'result_question_scores_result_id_foreign',
        'result_classifications' => 'result_classifications_result_id_foreign',
        'scoring_feedback'       => 'scoring_feedback_result_id_foreign',
    ];

    public function up(): void
    {
        // ── 1. Tạo bảng assessment_results ───────────────────────────────────
        Schema::create('assessment_results', function (Blueprint $table) {
            $table->id();

            // Polymorphic subject — phase 1: SurveyResponse; phase N: bất kỳ model nào
            $table->string('subject_type', 150)->comment('FQCN của model subject');
            $table->unsignedBigInteger('subject_id')->comment('PK của subject model');

            $table->decimal('overall_score', 5, 2)->nullable();
            $table->string('maturity_level', 64)->nullable()->comment('band_code hoặc persona_code');
            $table->string('assessment_code', 64)->not_null();
            $table->unsignedSmallInteger('weight_version')->default(1);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['subject_type', 'subject_id'], 'uq_ar_subject');
            $table->index('assessment_code', 'idx_ar_code');
            $table->index(['assessment_code', 'maturity_level'], 'idx_ar_code_band');
        });

        // ── 2. Migrate data từ survey_results → assessment_results ───────────
        // Dùng PDO binding thay vì raw string để tránh MySQL escape backslash
        DB::statement(
            "INSERT INTO assessment_results
                (id, subject_type, subject_id, overall_score, maturity_level,
                 assessment_code, weight_version, calculated_at, created_at, updated_at)
             SELECT
                id, ?, response_id, overall_score, maturity_level,
                assessment_code, COALESCE(weight_version, 1), calculated_at, created_at, updated_at
             FROM survey_results",
            [self::SUBJECT_TYPE_SURVEY]
        );

        // Reset AUTO_INCREMENT nếu cần (MySQL tự detect)
        $maxId = DB::table('assessment_results')->max('id') ?? 0;
        DB::statement("ALTER TABLE assessment_results AUTO_INCREMENT = " . ($maxId + 1));

        // ── 3. Update FK trên tất cả child tables ────────────────────────────
        foreach (self::CHILD_TABLES as $table => $constraintName) {
            Schema::table($table, function (Blueprint $t) use ($constraintName) {
                $t->dropForeign($constraintName);
            });
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->foreign('result_id')
                  ->references('id')
                  ->on('assessment_results')
                  ->cascadeOnDelete();
            });
        }

        // ── 4. Drop bảng survey_results ──────────────────────────────────────
        Schema::drop('survey_results');

        // ── 5. Tạo VIEW survey_results cho backward compat ───────────────────
        // Survey module's SurveyResult model vẫn đọc được data qua view này
        // MySQL string literal cần 4 backslash để lưu 1 backslash: \\\\ → \\ SQL → \ stored
        // FQCN: Modules\Survey\Models\SurveyResponse cần escape 3 dấu \ → 3 × \\\\ = 12 ký tự \\
        $type = self::SUBJECT_TYPE_SURVEY; // Modules\Survey\Models\SurveyResponse
        // Escape mỗi \ thành \\\\ để MySQL string literal hiểu đúng
        $mysqlEscaped = str_replace('\\', '\\\\\\\\', $type);
        DB::statement("
            CREATE VIEW survey_results AS
            SELECT
                id,
                subject_id   AS response_id,
                overall_score,
                maturity_level,
                assessment_code,
                weight_version,
                calculated_at,
                created_at,
                updated_at
            FROM assessment_results
            WHERE subject_type = '{$mysqlEscaped}'
        ");
    }

    public function down(): void
    {
        // ── 1. Drop view ──────────────────────────────────────────────────────
        DB::statement('DROP VIEW IF EXISTS survey_results');

        // ── 2. Recreate survey_results table ─────────────────────────────────
        Schema::create('survey_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')
                  ->unique()
                  ->constrained('survey_responses')
                  ->cascadeOnDelete();
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->string('maturity_level', 50)->nullable();
            $table->string('assessment_code', 50);
            $table->integer('weight_version')->default(1);
            $table->string('lead_temperature', 10)->nullable();
            $table->timestamp('calculated_at');
            $table->timestamps();
            $table->index(['assessment_code', 'maturity_level']);
        });

        // ── 3. Migrate data back ──────────────────────────────────────────────
        DB::statement(
            "INSERT INTO survey_results
                (id, response_id, overall_score, maturity_level,
                 assessment_code, weight_version, calculated_at, created_at, updated_at)
             SELECT id, subject_id, overall_score, maturity_level,
                assessment_code, weight_version, calculated_at, created_at, updated_at
             FROM assessment_results WHERE subject_type = ?",
            [self::SUBJECT_TYPE_SURVEY]
        );

        // ── 4. Re-point child FKs back to survey_results ─────────────────────
        foreach (self::CHILD_TABLES as $table => $constraintName) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['result_id']);
            });
            Schema::table($table, function (Blueprint $t) {
                $t->foreign('result_id')
                  ->references('id')
                  ->on('survey_results')
                  ->cascadeOnDelete();
            });
        }

        // ── 5. Drop assessment_results ────────────────────────────────────────
        Schema::dropIfExists('assessment_results');
    }
};

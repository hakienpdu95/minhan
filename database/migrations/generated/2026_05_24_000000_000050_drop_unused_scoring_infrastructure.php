<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop premature infrastructure that has no active code path:
 *   - Behavior Fi columns from score_rules (needs empirical calibration before useful)
 *   - feature_weights + feature_weight_history (WeightRepository always fell back to static)
 *   - tuning_schedule_config + tuning_cycles + feedback_sources_config (Wave 4, skipped)
 *
 * Keep: scoring_feedback (seeded by ResultPersister, foundation for future tuning)
 * Keep: submission_behavior_log (data collection continues, used later)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Revert behavior columns from score_rules ───────────────────────
        Schema::table('score_rules', function (Blueprint $table) {
            $table->dropUnique('uq_score_rule_v2');
            $table->dropColumn(['behavior_metric', 'threshold_value', 'operator', 'score_adjustment']);
            $table->unique(['assessment_code', 'field_key', 'domain_code'], 'uq_score_rule');
        });

        // ── 2. Drop dynamic weight tables ─────────────────────────────────────
        Schema::dropIfExists('feature_weight_history');
        Schema::dropIfExists('feature_weights');

        // ── 3. Drop tuning / feedback-source tables ───────────────────────────
        Schema::dropIfExists('tuning_cycles');
        Schema::dropIfExists('tuning_schedule_config');
        Schema::dropIfExists('feedback_sources_config');
    }

    public function down(): void
    {
        // Re-add behavior columns
        Schema::table('score_rules', function (Blueprint $table) {
            $table->dropUnique('uq_score_rule');
            $table->string('behavior_metric', 30)->nullable()->after('section_id');
            $table->decimal('threshold_value', 8, 2)->nullable()->after('behavior_metric');
            $table->string('operator', 4)->nullable()->after('threshold_value');
            $table->integer('score_adjustment')->nullable()->after('operator');
            $table->unique(['assessment_code', 'field_key', 'domain_code', 'condition_type'], 'uq_score_rule_v2');
        });

        // Re-create feature weight tables
        Schema::create('feature_weights', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code', 50);
            $table->string('feature_code', 100);
            $table->string('domain_code', 50)->nullable();
            $table->enum('weight_level', ['domain', 'feature'])->default('domain');
            $table->decimal('weight', 8, 4);
            $table->decimal('default_weight', 8, 4);
            $table->decimal('weight_min', 8, 4)->default(0);
            $table->decimal('weight_max', 8, 4)->default(1);
            $table->integer('version')->default(1);
            $table->enum('updated_by', ['manual', 'rule_based', 'ml_model'])->default('manual');
            $table->timestamps();
            $table->unique(['assessment_code', 'feature_code']);
        });

        Schema::create('feature_weight_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_weight_id')->constrained('feature_weights')->cascadeOnDelete();
            $table->decimal('old_weight', 8, 4);
            $table->decimal('new_weight', 8, 4);
            $table->decimal('delta', 8, 4);
            $table->string('reason', 255)->nullable();
            $table->unsignedBigInteger('cycle_id')->nullable();
            $table->timestamps();
        });

        Schema::create('tuning_schedule_config', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code', 50)->unique();
            $table->boolean('is_auto_tuning_enabled')->default(false);
            $table->integer('min_feedback_to_trigger')->default(30);
            $table->integer('max_cooldown_days')->default(30);
            $table->decimal('learning_rate', 6, 4)->default(0.05);
            $table->decimal('max_weight_change_pct', 5, 2)->default(10);
            $table->timestamp('last_cycle_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tuning_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code', 50);
            $table->integer('cycle_number');
            $table->enum('method', ['rule_based', 'ml_model']);
            $table->integer('feedback_count');
            $table->decimal('error_before', 8, 4)->nullable();
            $table->decimal('error_after', 8, 4)->nullable();
            $table->enum('status', ['pending', 'running', 'completed', 'rolled_back'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['assessment_code', 'cycle_number']);
        });

        Schema::create('feedback_sources_config', function (Blueprint $table) {
            $table->id();
            $table->enum('source_type', ['admin_review', 'observed_outcome', 'user_self_report']);
            $table->decimal('trust_weight', 4, 2);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Versioned snapshot of a full assessment scoring config.
 * 12 relational tables — no JSON columns.
 *
 * Rollup:
 *   assessment_config_snapshots   → snapshot_domains
 *                                 → snapshot_bands
 *                                 → snapshot_rules → snapshot_rule_options
 *                                                 → snapshot_rule_ranges
 *                                 → snapshot_personas → snapshot_persona_conditions
 *                                 → snapshot_pain_points
 *                                 → snapshot_recommendations
 *                                 → snapshot_roadmap_phases → snapshot_roadmap_milestones
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Snapshot header (assessment-level settings + pass-fail inline) ──
        Schema::create('assessment_config_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code', 60)->index();
            $table->unsignedInteger('version');
            // Assessment settings
            $table->boolean('has_scoring')->default(false);
            $table->string('aggregation_model', 30)->nullable();
            $table->string('classification_type', 30)->nullable();
            // Pass-fail (nullable = not configured)
            $table->decimal('passing_score', 5, 2)->nullable();
            $table->string('label_pass', 60)->nullable();
            $table->string('label_fail', 60)->nullable();
            // Audit
            $table->string('created_by', 255)->nullable();
            $table->text('change_note')->nullable();
            $table->timestamps();
            $table->unique(['assessment_code', 'version']);
        });

        // ── 2. Domains ────────────────────────────────────────────────────────
        Schema::create('snapshot_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('assessment_config_snapshots')->cascadeOnDelete();
            $table->string('domain_code', 60);
            $table->string('label', 120)->nullable();
            $table->decimal('weight', 8, 4)->default(0);
            $table->smallInteger('min_score')->default(0);
            $table->smallInteger('max_score')->default(100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->index('snapshot_id');
        });

        // ── 3. Score bands ────────────────────────────────────────────────────
        Schema::create('snapshot_bands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('assessment_config_snapshots')->cascadeOnDelete();
            $table->string('band_code', 60);
            $table->string('label', 120);
            $table->text('description')->nullable();
            $table->decimal('min_score', 5, 2)->default(0);
            $table->decimal('max_score', 5, 2)->default(100);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->index('snapshot_id');
        });

        // ── 4. Score rules ────────────────────────────────────────────────────
        Schema::create('snapshot_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('assessment_config_snapshots')->cascadeOnDelete();
            $table->string('field_key', 100);
            $table->string('domain_code', 60)->nullable();
            $table->string('feature_code', 100)->nullable();
            $table->string('signal_flag', 100)->nullable();
            $table->integer('score_if_true')->default(0);
            $table->integer('score_if_false')->default(0);
            $table->string('question_scoring_type', 30)->default('none');
            $table->string('condition_type', 30)->default('none');
            $table->integer('min_score_cap')->nullable();
            $table->integer('max_score_cap')->nullable();
            $table->index('snapshot_id');
        });

        // ── 5. Rule options ───────────────────────────────────────────────────
        Schema::create('snapshot_rule_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_rule_id')->constrained('snapshot_rules')->cascadeOnDelete();
            $table->string('option_value', 255);
            $table->string('option_label', 255)->nullable();
            $table->integer('score')->default(0);
            $table->string('signal_flag', 100)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->index('snapshot_rule_id');
        });

        // ── 6. Rule numeric ranges ─────────────────────────────────────────────
        Schema::create('snapshot_rule_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_rule_id')->constrained('snapshot_rules')->cascadeOnDelete();
            $table->decimal('min_value', 10, 2)->nullable();
            $table->decimal('max_value', 10, 2)->nullable();
            $table->integer('score')->default(0);
            $table->string('signal_flag', 100)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->index('snapshot_rule_id');
        });

        // ── 7. Personas ───────────────────────────────────────────────────────
        Schema::create('snapshot_personas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('assessment_config_snapshots')->cascadeOnDelete();
            $table->string('persona_code', 60);
            $table->string('label', 120);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->index('snapshot_id');
        });

        // ── 8. Persona conditions ─────────────────────────────────────────────
        Schema::create('snapshot_persona_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_persona_id')->constrained('snapshot_personas')->cascadeOnDelete();
            $table->string('target_type', 30);
            $table->string('target_code', 100)->nullable();
            $table->string('operator', 20);
            $table->decimal('threshold_value', 8, 4)->nullable();
            $table->tinyInteger('flag_value')->nullable(); // 0=false, 1=true, null=not set
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->index('snapshot_persona_id');
        });

        // ── 9. Pain point rules ────────────────────────────────────────────────
        Schema::create('snapshot_pain_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('assessment_config_snapshots')->cascadeOnDelete();
            $table->string('pain_point_code', 100);
            $table->string('label', 255)->nullable();
            $table->string('required_flags', 500); // CSV of flag codes, mirrors live table format
            $table->index('snapshot_id');
        });

        // ── 10. Recommendation rules ───────────────────────────────────────────
        Schema::create('snapshot_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('assessment_config_snapshots')->cascadeOnDelete();
            $table->string('recommendation_code', 100);
            $table->string('label', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('trigger_domain', 60)->nullable();
            $table->decimal('threshold_score', 5, 2)->default(50);
            $table->unsignedSmallInteger('priority')->default(1);
            $table->index('snapshot_id');
        });

        // ── 11. Roadmap phases ────────────────────────────────────────────────
        Schema::create('snapshot_roadmap_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_id')->constrained('assessment_config_snapshots')->cascadeOnDelete();
            $table->string('band_code', 60)->nullable();
            $table->string('phase_code', 100);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_weeks')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->index('snapshot_id');
        });

        // ── 12. Roadmap milestones ────────────────────────────────────────────
        Schema::create('snapshot_roadmap_milestones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('snapshot_phase_id')->constrained('snapshot_roadmap_phases')->cascadeOnDelete();
            $table->string('title', 255);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->index('snapshot_phase_id');
        });
    }

    public function down(): void
    {
        // Drop in reverse FK order
        Schema::dropIfExists('snapshot_roadmap_milestones');
        Schema::dropIfExists('snapshot_roadmap_phases');
        Schema::dropIfExists('snapshot_recommendations');
        Schema::dropIfExists('snapshot_pain_points');
        Schema::dropIfExists('snapshot_persona_conditions');
        Schema::dropIfExists('snapshot_personas');
        Schema::dropIfExists('snapshot_rule_ranges');
        Schema::dropIfExists('snapshot_rule_options');
        Schema::dropIfExists('snapshot_rules');
        Schema::dropIfExists('snapshot_bands');
        Schema::dropIfExists('snapshot_domains');
        Schema::dropIfExists('assessment_config_snapshots');
    }
};

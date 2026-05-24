<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tuning_schedule_config', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code', 50)->unique();
            $table->boolean('is_auto_tuning_enabled')->default(false)
                ->comment('Công tắc tổng — mặc định TẮT');
            $table->integer('min_feedback_to_trigger')->default(30);
            $table->integer('max_cooldown_days')->default(30);
            $table->decimal('learning_rate', 6, 4)->default(0.05)
                ->comment('Bước nhảy cố định, nhỏ');
            $table->decimal('max_weight_change_pct', 5, 2)->default(10.00)
                ->comment('Cap 10%/chu kỳ');
            $table->timestamp('last_cycle_at')->nullable();
            $table->timestamps();
        });

        Schema::create('tuning_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code', 50);
            $table->integer('cycle_number');
            $table->string('method', 20)->comment('rule_based | ml_model');
            $table->integer('feedback_count');
            $table->decimal('error_before', 8, 4)->nullable();
            $table->decimal('error_after', 8, 4)->nullable();
            $table->string('status', 20)->default('pending')
                ->comment('pending | running | completed | rolled_back');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['assessment_code', 'cycle_number'], 'uq_tuning_cycle');
            $table->index('assessment_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tuning_cycles');
        Schema::dropIfExists('tuning_schedule_config');
    }
};

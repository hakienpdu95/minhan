<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code', 50)->comment('FK logic tới assessments.assessment_code');
            $table->string('persona_code', 100);
            $table->string('label', 255);
            $table->text('description')->nullable();
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['assessment_code', 'persona_code'], 'uq_persona');
            $table->index('assessment_code');
        });

        Schema::create('persona_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('personas')->cascadeOnDelete();
            $table->string('target_type', 30)
                ->comment('domain | section | overall | signal_flag');
            $table->string('target_code', 100)
                ->comment('domain_code | section_code | "overall" | flag_code');
            $table->string('operator', 5)
                ->comment('< | <= | = | >= | >');
            $table->decimal('threshold_value', 5, 2)->nullable()
                ->comment('Ngưỡng cho domain/section/overall');
            $table->boolean('flag_value')->nullable()
                ->comment('Giá trị mong đợi cho signal_flag');
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('persona_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persona_conditions');
        Schema::dropIfExists('personas');
    }
};

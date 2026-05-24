<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmap_phases', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code', 50);
            $table->string('maturity_level', 50)->comment('Khớp với maturity_levels.level_code');
            $table->string('phase_code', 100);
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->tinyInteger('duration_weeks')->nullable()->comment('Thời gian dự kiến (tuần)');
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['assessment_code', 'maturity_level', 'phase_code'], 'uq_roadmap_phase');
            $table->index(['assessment_code', 'maturity_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_phases');
    }
};

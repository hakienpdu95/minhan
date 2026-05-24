<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_roadmap_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('survey_results')->cascadeOnDelete();
            $table->foreignId('phase_id')->constrained('roadmap_phases')->cascadeOnDelete();
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['result_id', 'phase_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_roadmap_phases');
    }
};

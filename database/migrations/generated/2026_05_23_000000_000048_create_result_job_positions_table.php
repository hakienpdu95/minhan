<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_job_positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('survey_results')->cascadeOnDelete();
            $table->string('position_code', 50);
            $table->decimal('match_score', 5, 2)->comment('Percentage 0-100');
            $table->timestamps();

            $table->index(['result_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_job_positions');
    }
};

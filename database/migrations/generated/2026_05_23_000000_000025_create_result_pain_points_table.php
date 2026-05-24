<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_pain_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('survey_results')->cascadeOnDelete();
            $table->string('pain_point_code', 100);
            $table->timestamps();

            $table->unique(['result_id', 'pain_point_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_pain_points');
    }
};

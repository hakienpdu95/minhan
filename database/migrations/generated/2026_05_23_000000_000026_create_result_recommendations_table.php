<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('survey_results')->cascadeOnDelete();
            $table->string('recommendation_code', 100);
            $table->tinyInteger('priority')->default(1);
            $table->timestamps();

            $table->unique(['result_id', 'recommendation_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_recommendations');
    }
};

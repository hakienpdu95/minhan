<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_domain_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('survey_results')->cascadeOnDelete();
            $table->string('domain_code', 50);
            $table->integer('raw_score');
            $table->decimal('normalized_score', 5, 2);
            $table->timestamps();

            $table->unique(['result_id', 'domain_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_domain_scores');
    }
};

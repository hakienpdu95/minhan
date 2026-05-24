<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->unique()->constrained('survey_responses')->cascadeOnDelete();
            $table->decimal('overall_score', 5, 2);
            $table->string('maturity_level', 50)->comment('Khớp với maturity_levels.level_code');
            $table->string('assessment_code', 50)->comment('Version config dùng để tính');
            $table->timestamp('calculated_at')->useCurrent();
            $table->timestamps();

            $table->index(['assessment_code', 'maturity_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_results');
    }
};

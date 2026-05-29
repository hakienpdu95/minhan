<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_question_selected_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_score_id')
                  ->constrained('result_question_scores')
                  ->onDelete('cascade');
            $table->string('option_key', 128);
            $table->index('question_score_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_question_selected_options');
    }
};

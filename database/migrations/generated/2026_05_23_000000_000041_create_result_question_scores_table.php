<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_question_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('survey_results')->cascadeOnDelete();
            $table->string('question_code', 100)->comment('field_key của câu hỏi');
            $table->string('feature_code', 100)->comment('feature_code từ score_rules');
            $table->integer('raw_score')->comment('Score trước khi cap');
            $table->integer('final_score')->comment('Score sau cap = Fi');
            $table->string('selected_options', 500)->nullable()
                ->comment('Comma-separated option values đã chọn');
            $table->timestamps();

            $table->unique(['result_id', 'question_code'], 'uq_rqs');
            $table->index('result_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_question_scores');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submission_behavior_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('survey_responses')->cascadeOnDelete()
                ->comment('FK -> survey_responses (= submission_id trong spec)');
            $table->string('question_code', 100)->nullable()
                ->comment('field_key của câu hỏi liên quan');
            $table->string('event_type', 30)
                ->comment('view | answer | change | skip | back | time_spent');
            $table->string('event_value', 255)->nullable()
                ->comment('Giá trị kèm theo event (e.g. thời gian ms, option chọn)');
            $table->integer('sequence_no')->comment('Thứ tự event trong session');
            $table->timestamp('occurred_at')->useCurrent();

            $table->index(['response_id', 'sequence_no']);
            $table->index('question_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_behavior_log');
    }
};

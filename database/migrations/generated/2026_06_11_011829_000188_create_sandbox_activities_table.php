<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sandbox_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sandbox_session_id');
            $table->string('activity_type', 50)
                ->comment('prompt_used|tool_called|output_generated|error_occurred|iteration');
            $table->string('activity_description', 500)->comment('Mô tả ngắn hành động');
            $table->string('ai_tool_used', 100)->nullable();
            $table->unsignedTinyInteger('quality_note')->nullable()->comment('0–10 evaluator note');
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->foreign('sandbox_session_id')->references('id')->on('sandbox_sessions')->cascadeOnDelete();
            $table->index(['sandbox_session_id', 'activity_type'], 'idx_sandact_session_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sandbox_activities');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->string('ai_action_type', 30)->nullable()->after('event')
                ->comment('scoring|recommendation|certification_check|matching|null nếu không phải AI');

            $table->string('ai_model_used', 50)->nullable()->after('ai_action_type')
                ->comment('GPT-4o|Claude-3.5|Gemini-Pro|Scoring-Engine-v1...');

            $table->string('ai_risk_level', 10)->nullable()->after('ai_model_used')
                ->comment('low|medium|high — phân loại rủi ro AI output');

            $table->boolean('human_reviewed')->default(false)->after('ai_risk_level')
                ->comment('true nếu đã có người kiểm tra kết quả AI');

            $table->unsignedBigInteger('human_reviewer_id')->nullable()->after('human_reviewed');
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn([
                'ai_action_type',
                'ai_model_used',
                'ai_risk_level',
                'human_reviewed',
                'human_reviewer_id',
            ]);
        });
    }
};

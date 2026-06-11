<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->string('source_type', 20)->nullable()->after('respondent_ref')
                ->comment('self | manager | expert | data');

            $table->unsignedBigInteger('subject_user_id')->nullable()->after('source_type')
                ->comment('user_id người được đánh giá — null khi source_type=self');

            $table->unsignedBigInteger('evaluator_user_id')->nullable()->after('subject_user_id')
                ->comment('user_id người đánh giá — null khi ẩn danh hoặc source=data');

            $table->decimal('source_weight', 4, 2)->nullable()->after('evaluator_user_id')
                ->comment('Trọng số nguồn đánh giá — 0.25|0.30|0.25|0.20 (configurable)');

            $table->boolean('requires_human_review')->default(false)->after('source_weight')
                ->comment('True khi độ lệch giữa các nguồn vượt ngưỡng — kích hoạt Human-in-the-Loop');

            $table->index(['subject_user_id', 'source_type'], 'idx_sr_subject_source');
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropIndex('idx_sr_subject_source');
            $table->dropColumn([
                'source_type',
                'subject_user_id',
                'evaluator_user_id',
                'source_weight',
                'requires_human_review',
            ]);
        });
    }
};

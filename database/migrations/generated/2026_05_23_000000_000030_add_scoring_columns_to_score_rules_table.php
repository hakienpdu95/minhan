<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('score_rules', function (Blueprint $table) {
            $table->string('feature_code', 100)->nullable()
                ->comment('Định danh đặc trưng Fi — cầu nối với feature_weights. Mặc định = field_key')
                ->after('domain_code');

            $table->string('question_scoring_type', 30)->nullable()
                ->comment('none | boolean | single_choice | multi_choice | numeric_range. NULL = dùng condition_type')
                ->after('feature_code');

            $table->integer('min_score_cap')->nullable()
                ->comment('multi_choice: chặn dưới tổng score')
                ->after('question_scoring_type');

            $table->integer('max_score_cap')->nullable()
                ->comment('multi_choice: chặn trên tổng score')
                ->after('min_score_cap');

            $table->foreignId('section_id')->nullable()
                ->constrained('survey_sections')->nullOnDelete()
                ->comment('FK -> survey_sections, dùng cho sectioned aggregation')
                ->after('max_score_cap');
        });

        // Khởi tạo feature_code = field_key cho các rows đã có
        \Illuminate\Support\Facades\DB::statement(
            'UPDATE score_rules SET feature_code = field_key WHERE feature_code IS NULL'
        );
    }

    public function down(): void
    {
        Schema::table('score_rules', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn(['feature_code', 'question_scoring_type', 'min_score_cap', 'max_score_cap', 'section_id']);
        });
    }
};

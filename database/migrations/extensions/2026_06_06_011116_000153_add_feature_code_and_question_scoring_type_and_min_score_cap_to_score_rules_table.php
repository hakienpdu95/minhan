<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('score_rules', function (Blueprint $table) {
            $table->string('feature_code', 100)->nullable()->comment('Định danh đặc trưng Fi — cầu nối với feature_weights. Mặc định = field_key');
            $table->string('question_scoring_type', 30)->nullable()->after('feature_code')->comment('none | boolean | single_choice | multi_choice | numeric_range. NULL = dùng condition_type');
            $table->integer('min_score_cap')->nullable()->after('question_scoring_type')->comment('multi_choice: chặn dưới tổng score');
            $table->integer('max_score_cap')->nullable()->after('min_score_cap')->comment('multi_choice: chặn trên tổng score');
            $table->foreignId('section_id')->nullable()->constrained('survey_sections')->nullOnDelete()->after('max_score_cap')->comment('FK -> survey_sections, dùng cho sectioned aggregation');
            $table->unique(['assessment_code', 'field_key', 'domain_code', 'condition_type'], 'uq_score_rule_v2');
        });
    }

    public function down(): void
    {
        Schema::table('score_rules', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn(['feature_code', 'question_scoring_type', 'min_score_cap', 'max_score_cap', 'section_id']);
        });
    }
};
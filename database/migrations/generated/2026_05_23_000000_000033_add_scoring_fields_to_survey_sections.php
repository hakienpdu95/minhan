<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_sections', function (Blueprint $table) {
            $table->string('section_code', 50)->nullable()
                ->comment('Code định danh section — dùng cho sectioned aggregation')
                ->after('survey_id');

            $table->string('assessment_code', 50)->nullable()
                ->comment('Gắn section vào assessment — dùng cho sectioned aggregation')
                ->after('section_code');

            $table->integer('min_score')->default(0)
                ->comment('Raw score thấp nhất lý thuyết (dùng cho normalize)')
                ->after('assessment_code');

            $table->integer('max_score')->default(100)
                ->comment('Raw score cao nhất lý thuyết (dùng cho normalize)')
                ->after('min_score');
        });
    }

    public function down(): void
    {
        Schema::table('survey_sections', function (Blueprint $table) {
            $table->dropColumn(['section_code', 'assessment_code', 'min_score', 'max_score']);
        });
    }
};

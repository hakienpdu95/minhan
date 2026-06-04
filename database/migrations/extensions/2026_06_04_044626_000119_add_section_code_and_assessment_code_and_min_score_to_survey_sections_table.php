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
        Schema::table('survey_sections', function (Blueprint $table) {
            $table->string('section_code', 50)->nullable()->comment('Code định danh section — dùng cho sectioned aggregation');
            $table->string('assessment_code', 50)->nullable()->after('section_code')->comment('Gắn section vào assessment — dùng cho sectioned aggregation');
            $table->integer('min_score')->default(0)->after('assessment_code')->comment('Raw score thấp nhất lý thuyết (dùng cho normalize)');
            $table->integer('max_score')->default(100)->after('min_score')->comment('Raw score cao nhất lý thuyết (dùng cho normalize)');
        });
    }

    public function down(): void
    {
        Schema::table('survey_sections', function (Blueprint $table) {
            $table->dropColumn(['section_code', 'assessment_code', 'min_score', 'max_score']);
        });
    }
};
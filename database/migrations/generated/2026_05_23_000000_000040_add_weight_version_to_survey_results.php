<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_results', function (Blueprint $table) {
            $table->integer('weight_version')->default(1)
                ->comment('Version bộ feature_weights đã dùng — truy vết')
                ->after('assessment_code');
        });
    }

    public function down(): void
    {
        Schema::table('survey_results', function (Blueprint $table) {
            $table->dropColumn('weight_version');
        });
    }
};

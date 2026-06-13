<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (Schema::hasIndex('surveys', 'surveys_assessment_code_unique')) {
                $table->dropUnique('surveys_assessment_code_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (!Schema::hasIndex('surveys', 'surveys_assessment_code_unique')) {
                $table->unique('assessment_code');
            }
        });
    }
};

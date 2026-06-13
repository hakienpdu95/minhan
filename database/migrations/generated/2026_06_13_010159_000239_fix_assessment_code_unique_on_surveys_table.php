<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            // Drop single-column unique — multiple specialized surveys share the same assessment_code
            $table->dropUnique('surveys_assessment_code_unique');

            // Composite unique: one survey per (assessment_code, specialized_set_code) pair
            // NULL specialized_set_code = the "main" survey for that assessment_code
            $table->unique(['assessment_code', 'specialized_set_code'], 'surveys_assessment_specialized_unique');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropUnique('surveys_assessment_specialized_unique');
            $table->unique('assessment_code', 'surveys_assessment_code_unique');
        });
    }
};

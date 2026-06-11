<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // surveys.assessment_code was unique (1 survey per assessment), but TDWCF
    // supports multiple specialized survey sets (B1–B7) sharing the same
    // assessment_code. Drop the unique index; slugs already guarantee uniqueness.
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropUnique('surveys_assessment_code_unique');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->unique('assessment_code', 'surveys_assessment_code_unique');
        });
    }
};

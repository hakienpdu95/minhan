<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->string('specialized_set_code', 20)->nullable()->after('assessment_code')
                ->comment('B1_SALES|B2_HR|B3_FINANCE|B4_OPS|B5_IT|B6_LEADERSHIP|B7_EDUCATION');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn('specialized_set_code');
        });
    }
};

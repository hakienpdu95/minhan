<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->string('assessment_code', 50)->nullable()->unique()->after('slug')
                ->comment('Mã định danh cho Scoring Engine — nullable nếu survey không có scoring');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn('assessment_code');
        });
    }
};

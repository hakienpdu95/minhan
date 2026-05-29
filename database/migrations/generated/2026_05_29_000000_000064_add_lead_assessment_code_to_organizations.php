<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('lead_assessment_code', 64)
                  ->nullable()
                  ->after('slug')
                  ->comment('Assessment code dùng để chấm điểm lead sâu. NULL = tắt.');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn('lead_assessment_code');
        });
    }
};

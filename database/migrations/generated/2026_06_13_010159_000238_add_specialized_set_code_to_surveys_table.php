<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (!Schema::hasColumn('surveys', 'specialized_set_code')) {
                $table->string('specialized_set_code', 50)->nullable()->after('assessment_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            if (Schema::hasColumn('surveys', 'specialized_set_code')) {
                $table->dropColumn('specialized_set_code');
            }
        });
    }
};

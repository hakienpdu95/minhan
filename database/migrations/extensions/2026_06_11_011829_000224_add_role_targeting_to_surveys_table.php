<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->string('target_role_code', 100)->nullable()->after('specialized_set_code')
                ->comment('job_title.code — khảo sát dành riêng cho vị trí này');

            $table->string('target_role_level', 30)->nullable()->after('target_role_code')
                ->comment('junior|senior|lead|manager');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn(['target_role_code', 'target_role_level']);
        });
    }
};

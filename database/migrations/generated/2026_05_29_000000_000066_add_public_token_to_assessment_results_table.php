<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assessment_results', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable()->unique()->after('assessment_code')
                  ->comment('Token cho phép xem kết quả công khai không cần đăng nhập');
        });
    }

    public function down(): void
    {
        Schema::table('assessment_results', function (Blueprint $table) {
            $table->dropColumn('public_token');
        });
    }
};

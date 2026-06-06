<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('employee_history', function (Blueprint $table) {
            $table->decimal('old_salary_base', 15, 2)->nullable();
            $table->decimal('new_salary_base', 15, 2)->nullable()->after('old_salary_base');
        });
    }

    public function down(): void
    {
        Schema::table('employee_history', function (Blueprint $table) {
            $table->dropColumn(['old_salary_base', 'new_salary_base']);
        });
    }
};
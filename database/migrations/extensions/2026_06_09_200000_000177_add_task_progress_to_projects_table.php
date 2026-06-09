<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->unsignedSmallInteger('progress_pct')->default(0)->after('currency');
            $table->unsignedSmallInteger('task_total')->default(0)->after('progress_pct');
            $table->unsignedSmallInteger('task_done')->default(0)->after('task_total');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['progress_pct', 'task_total', 'task_done']);
        });
    }
};

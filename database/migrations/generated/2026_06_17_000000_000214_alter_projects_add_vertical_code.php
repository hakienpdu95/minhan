<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // null = project thường (không thuộc vertical nào)
            $table->string('vertical_code', 50)->nullable()->after('category')->index();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['vertical_code']);
            $table->dropColumn('vertical_code');
        });
    }
};

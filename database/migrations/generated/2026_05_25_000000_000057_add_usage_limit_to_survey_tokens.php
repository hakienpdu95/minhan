<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_tokens', function (Blueprint $table) {
            $table->unsignedInteger('usage_limit')->nullable()->after('expires_at'); // null = không giới hạn
            $table->unsignedInteger('usage_count')->default(0)->after('usage_limit');
        });
    }

    public function down(): void
    {
        Schema::table('survey_tokens', function (Blueprint $table) {
            $table->dropColumn(['usage_limit', 'usage_count']);
        });
    }
};

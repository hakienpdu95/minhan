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
        Schema::table('vertical_config_items', function (Blueprint $table) {
            if (Schema::hasColumn('vertical_config_items', 'organization_id')) $table->dropForeign(['organization_id']);
            $cols = array_filter(['organization_id', 'vertical_code'], fn($c) => Schema::hasColumn('vertical_config_items', $c));
            if (!empty($cols)) $table->dropColumn(array_values($cols));
        });
    }

    public function down(): void
    {
        Schema::table('vertical_config_items', function (Blueprint $table) {
            // TODO: $table->unsignedBigInteger('organization_id')->...; // add lại 'organization_id'
            // TODO: $table->string('vertical_code')->...; // add lại 'vertical_code'
        });
    }
};
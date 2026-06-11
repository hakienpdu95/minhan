<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('notifications', 'organization_id')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->unsignedBigInteger('organization_id')->nullable()->index()->after('id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('organization_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pain_point_rules', function (Blueprint $table) {
            $table->string('label')->nullable()->change();
        });

        Schema::table('recommendation_rules', function (Blueprint $table) {
            $table->string('label')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pain_point_rules', function (Blueprint $table) {
            $table->string('label')->nullable(false)->change();
        });

        Schema::table('recommendation_rules', function (Blueprint $table) {
            $table->string('label')->nullable(false)->change();
        });
    }
};

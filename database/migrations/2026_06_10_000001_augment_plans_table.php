<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->string('tag_line', 120)->nullable()->after('slug');
            $table->string('tier', 32)->default('growth')->after('tag_line');
            $table->string('badge_color', 64)->nullable()->after('tier');
            $table->boolean('is_public')->default(true)->after('badge_color');
            $table->decimal('annual_price', 15, 2)->nullable()->after('price');
            $table->string('currency_local', 10)->default('VND');
            $table->decimal('price_local', 15, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table): void {
            $table->dropColumn([
                'tag_line', 'tier', 'badge_color', 'is_public',
                'annual_price', 'currency_local', 'price_local',
            ]);
        });
    }
};

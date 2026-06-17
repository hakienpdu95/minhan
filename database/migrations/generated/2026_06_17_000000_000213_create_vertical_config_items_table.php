<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vertical_config_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('vertical_code', 50);
            $table->string('config_group', 50); // 'activity_type' | 'doc_type' | 'item_type' | 'hierarchy'
            $table->string('code', 50);
            $table->string('label', 255);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->unsigned()->default(0);

            $table->unique(['organization_id', 'vertical_code', 'config_group', 'code'], 'uq_vertical_config_item');
            $table->index(['organization_id', 'vertical_code', 'config_group', 'is_active'], 'idx_vertical_config_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vertical_config_items');
    }
};

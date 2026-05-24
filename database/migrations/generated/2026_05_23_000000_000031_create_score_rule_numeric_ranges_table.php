<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_rule_numeric_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('score_rules')->cascadeOnDelete();
            $table->decimal('min_value', 10, 2)->nullable()->comment('NULL = không giới hạn dưới');
            $table->decimal('max_value', 10, 2)->nullable()->comment('NULL = không giới hạn trên');
            $table->integer('score')->notNull();
            $table->string('signal_flag', 100)->nullable();
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('rule_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_rule_numeric_ranges');
    }
};

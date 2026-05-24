<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_rule_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rule_id')->constrained('score_rules')->cascadeOnDelete();
            $table->string('option_value', 100)->comment('Khớp với survey_field_options.option_value');
            $table->integer('score')->default(0);
            $table->string('signal_flag', 100)->nullable()->comment('Flag emit khi option này được chọn');
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['rule_id', 'option_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_rule_options');
    }
};

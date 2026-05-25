<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_field_conditions', function (Blueprint $table) {
            $table->id();
            // Field sẽ được hiện/ẩn
            $table->foreignId('field_id')->constrained('survey_fields')->cascadeOnDelete();
            // Field điều kiện (field cha)
            $table->foreignId('depends_on_field_id')->constrained('survey_fields')->cascadeOnDelete();
            $table->string('operator');     // '=', '!=', 'in', 'not_in', '>', '<', 'contains', 'answered'
            $table->json('trigger_value');  // giá trị / mảng giá trị kích hoạt
            $table->string('action')->default('show'); // 'show' | 'hide' | 'require'
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index('field_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_field_conditions');
    }
};

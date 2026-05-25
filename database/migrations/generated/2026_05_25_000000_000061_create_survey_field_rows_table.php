<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_field_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained('survey_fields')->cascadeOnDelete();
            $table->string('row_key', 100);
            $table->string('label', 255);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->unique(['field_id', 'row_key']);
            $table->index(['field_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_field_rows');
    }
};

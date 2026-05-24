<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('result_signal_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('survey_results')->cascadeOnDelete();
            $table->string('flag_code', 100)->comment('e.g. HAS_CRM');
            $table->boolean('flag_value');
            $table->timestamps();

            $table->unique(['result_id', 'flag_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('result_signal_flags');
    }
};

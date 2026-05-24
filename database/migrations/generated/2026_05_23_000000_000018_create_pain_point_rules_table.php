<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pain_point_rules', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code', 50);
            $table->string('pain_point_code', 100)->comment('e.g. sales_leakage');
            $table->string('label', 255);
            $table->string('required_flags', 500)
                ->comment('Comma-separated flags, prefix ! = NOT. Tất cả AND. e.g. LEAD_LOSS,!HAS_CRM');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['assessment_code', 'pain_point_code']);
            $table->index('assessment_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pain_point_rules');
    }
};

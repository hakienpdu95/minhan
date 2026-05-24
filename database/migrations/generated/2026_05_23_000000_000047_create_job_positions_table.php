<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code', 50)->index();
            $table->string('position_code', 50);
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('min_overall_score', 5, 2)->nullable()->comment('Minimum overall score to qualify');
            $table->json('requirements')->nullable()->comment('{"domain_code": min_normalized_score}');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['assessment_code', 'position_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_positions');
    }
};

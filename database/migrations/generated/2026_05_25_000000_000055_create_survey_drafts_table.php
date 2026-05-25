<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('respondent_ref')->nullable()->index();
            $table->json('answers');
            $table->unsignedInteger('current_section')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['survey_id', 'respondent_ref']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_drafts');
    }
};

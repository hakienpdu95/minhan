<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->string('secret', 255)->nullable();    // HMAC signing key
            $table->string('events', 500)->nullable();    // comma-separated: response.created,result.calculated
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('survey_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_webhooks');
    }
};

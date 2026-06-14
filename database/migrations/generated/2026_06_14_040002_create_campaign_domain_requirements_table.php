<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_domain_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->string('domain_code', 10);
            $table->decimal('min_score', 5, 2)->default(0);
            $table->tinyInteger('is_required')->default(1);

            $table->unique(['campaign_id', 'domain_code'], 'cdr_campaign_domain_unique');

            $table->foreign('campaign_id', 'cdr_campaign_fk')
                ->references('id')->on('open_assessment_campaigns')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_domain_requirements');
    }
};

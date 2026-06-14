<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_participation_scores', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('participation_id');
            $table->string('domain_code', 10);
            $table->decimal('score', 5, 2);

            $table->unique(['participation_id', 'domain_code'], 'cps_part_domain_unique');

            $table->foreign('participation_id', 'cps_participation_fk')
                ->references('id')->on('campaign_participations')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_participation_scores');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_sandbox_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('sandbox_task_id');
            $table->tinyInteger('is_required')->default(1);
            $table->unsignedTinyInteger('sort_order')->default(0);

            $table->unique(['campaign_id', 'sandbox_task_id'], 'cst_campaign_task_unique');
            $table->index('campaign_id', 'cst_campaign_index');

            $table->foreign('campaign_id', 'cst_campaign_fk')
                ->references('id')->on('open_assessment_campaigns')->cascadeOnDelete();
            $table->foreign('sandbox_task_id', 'cst_task_fk')
                ->references('id')->on('sandbox_tasks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_sandbox_tasks');
    }
};

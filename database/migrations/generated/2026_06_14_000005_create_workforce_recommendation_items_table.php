<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workforce_recommendation_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('workforce_recommendation_id');
            $table->foreign('workforce_recommendation_id', 'wri_recommendation_fk')
                ->references('id')->on('workforce_recommendations')
                ->cascadeOnDelete();
            $table->tinyInteger('priority')
                ->comment('1=cao nhất, 5=thấp nhất');
            $table->string('domain_code', 20)
                ->comment('D1|D2|D3|D4|D5|D6');
            $table->text('action_description');
            $table->string('resource_type', 30)
                ->comment('course | sandbox | certification | practice | reading');
            $table->string('resource_name', 300)->nullable();
            $table->string('resource_url', 500)->nullable();
            $table->decimal('estimated_duration_hours', 4, 1)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('workforce_recommendation_id', 'wri_recommendation_index');
            $table->index('domain_code', 'wri_domain_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workforce_recommendation_items');
    }
};

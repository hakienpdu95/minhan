<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_weight_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_weight_id')->constrained('feature_weights')->cascadeOnDelete();
            $table->decimal('old_weight', 8, 4);
            $table->decimal('new_weight', 8, 4);
            $table->decimal('delta', 8, 4);
            $table->string('reason', 255)->nullable();
            $table->unsignedBigInteger('cycle_id')->nullable()->comment('FK logic tới tuning_cycles.id');
            $table->timestamp('created_at')->useCurrent();

            $table->index('feature_weight_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_weight_history');
    }
};

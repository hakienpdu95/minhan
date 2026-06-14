<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passport_sandbox_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('passport_entry_id')
                ->constrained('passport_entries')->cascadeOnDelete();
            $table->unsignedBigInteger('sandbox_env_id')->nullable();
            $table->string('env_code', 50)->comment('Bất biến');
            $table->string('env_name', 200)->comment('Bất biến');
            $table->unsignedSmallInteger('sessions_completed')->default(0);
            $table->decimal('hours_spent', 5, 1)->nullable();
            $table->decimal('avg_score', 5, 2)->nullable();

            $table->unique(['passport_entry_id', 'env_code'], 'pss_entry_env_unique');
            $table->index('passport_entry_id', 'pss_entry_index');
        });

        Schema::table('passport_sandbox_summaries', function (Blueprint $table) {
            $table->foreign('sandbox_env_id', 'pss_sandbox_env_fk')
                ->references('id')->on('sandbox_environments')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passport_sandbox_summaries');
    }
};

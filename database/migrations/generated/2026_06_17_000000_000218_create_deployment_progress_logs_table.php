<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployment_progress_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deployment_target_id')->constrained()->cascadeOnDelete();
            $table->string('phase', 50);
            $table->tinyInteger('percent')->unsigned()->default(0);
            $table->text('remark')->nullable();
            $table->foreignId('logged_by')->constrained('users');
            $table->timestamp('logged_at')->useCurrent();

            $table->index('deployment_target_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_progress_logs');
    }
};

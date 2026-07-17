<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('business_project_stage_history')) {
            return;
        }

        Schema::create('business_project_stage_history', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique()->comment('Public UUID — expose ra ngoài, không phải PK');
            $table->unsignedInteger('order_column')->nullable()->index()->comment('Thứ tự sắp xếp — Spatie Sortable / ORDER BY');
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->string('stage_from', 30)->nullable();
            $table->string('stage_to', 30);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('changed_at');
            $table->timestamp('created_at')->nullable();
            

            // Indexes
            $table->index(['business_project_id', 'changed_at'], 'idx_bp_stage_history_project');
            $table->index(['organization_id', 'changed_at'], 'idx_bp_stage_history_org');
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('business_project_stage_history');
    }
};
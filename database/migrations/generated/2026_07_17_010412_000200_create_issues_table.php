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
        if (Schema::hasTable('issues')) {
            return;
        }

        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique()->comment('Public UUID — expose ra ngoài, không phải PK');
            $table->unsignedInteger('order_column')->nullable()->index()->comment('Thứ tự sắp xếp — Spatie Sortable / ORDER BY');
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium')->index();
            $table->enum('status', ['open', 'resolved', 'escalated'])->default('open')->index();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            

            // Indexes
            $table->index(['business_project_id', 'status']);
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
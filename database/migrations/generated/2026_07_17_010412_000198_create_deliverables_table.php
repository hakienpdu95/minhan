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
        if (Schema::hasTable('deliverables')) {
            return;
        }

        Schema::create('deliverables', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique()->comment('Public UUID — expose ra ngoài, không phải PK');
            $table->unsignedInteger('order_column')->nullable()->index()->comment('Thứ tự sắp xếp — Spatie Sortable / ORDER BY');
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->enum('workspace', ['context', 'discovery', 'diagnosis', 'transformation', 'delivery', 'closing', 'knowledge', 'customer_success'])->index();
            $table->string('type', 60)->index();
            $table->string('title', 255);
            $table->foreignId('parent_id')->nullable()->constrained('deliverables')->nullOnDelete();
            $table->unsignedInteger('current_version')->default(0);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'confirmed'])->default('draft')->index();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            

            // Indexes
            $table->index(['business_project_id', 'workspace']);
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverables');
    }
};
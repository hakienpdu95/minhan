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
        if (Schema::hasTable('change_requests')) {
            return;
        }

        Schema::create('change_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique()->comment('Public UUID — expose ra ngoài, không phải PK');
            $table->unsignedInteger('order_column')->nullable()->index()->comment('Thứ tự sắp xếp — Spatie Sortable / ORDER BY');
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->enum('source_type', ['issue', 'risk'])->index();
            $table->foreignId('issue_id')->nullable()->constrained('issues')->nullOnDelete();
            $table->foreignId('risk_id')->nullable()->constrained('risks')->nullOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->boolean('impacts_scope')->default(false);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft')->index();
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
        Schema::dropIfExists('change_requests');
    }
};
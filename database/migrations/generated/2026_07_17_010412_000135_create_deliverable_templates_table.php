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
        if (Schema::hasTable('deliverable_templates')) {
            return;
        }

        Schema::create('deliverable_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique()->comment('Public UUID — expose ra ngoài, không phải PK');
            $table->unsignedInteger('order_column')->nullable()->index()->comment('Thứ tự sắp xếp — Spatie Sortable / ORDER BY');
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type', 60);
            $table->string('name', 255);
            $table->string('description', 500)->nullable();
            $table->json('content');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            

            // Indexes
            $table->index(['type', 'is_active']);
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverable_templates');
    }
};
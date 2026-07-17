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
        if (Schema::hasTable('milestones')) {
            return;
        }

        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique()->comment('Public UUID — expose ra ngoài, không phải PK');
            $table->unsignedInteger('order_column')->nullable()->index()->comment('Thứ tự sắp xếp — Spatie Sortable / ORDER BY');
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['quick_win', 'day_30', 'day_90', 'day_365'])->index();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('target_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            

            // Indexes
            $table->index(['business_project_id', 'category']);
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};
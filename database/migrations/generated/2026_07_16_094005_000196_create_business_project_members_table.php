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
        if (Schema::hasTable('business_project_members')) {
            return;
        }

        Schema::create('business_project_members', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique()->comment('Public UUID — expose ra ngoài, không phải PK');
            $table->unsignedInteger('order_column')->nullable()->index()->comment('Thứ tự sắp xếp — Spatie Sortable / ORDER BY');
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('project_role', ['sponsor', 'owner', 'lead_consultant', 'consultant', 'ba', 'pm', 'customer_success'])->index();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();
            

            // Indexes
            $table->unique(['business_project_id', 'user_id']);
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('business_project_members');
    }
};
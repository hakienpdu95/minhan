<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('project_role', [
                'sponsor', 'owner', 'lead_consultant', 'consultant', 'ba', 'pm', 'customer_success',
            ])->index();
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamps();

            $table->unique(['business_project_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_project_members');
    }
};

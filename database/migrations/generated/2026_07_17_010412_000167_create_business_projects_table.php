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
        if (Schema::hasTable('business_projects')) {
            return;
        }

        Schema::create('business_projects', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique()->comment('Public UUID — expose ra ngoài, không phải PK');
            $table->unsignedInteger('order_column')->nullable()->index()->comment('Thứ tự sắp xếp — Spatie Sortable / ORDER BY');
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->restrictOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('code', 30);
            $table->string('name', 255);
            $table->enum('current_stage', ['context', 'discovery', 'diagnosis', 'transformation', 'delivery', 'closing', 'knowledge', 'customer_success'])->default('context')->index();
            $table->enum('status', ['active', 'on_hold', 'closed', 'cancelled'])->default('active')->index();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            

            // Indexes
            $table->unique(['organization_id', 'code']);
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('business_projects');
    }
};
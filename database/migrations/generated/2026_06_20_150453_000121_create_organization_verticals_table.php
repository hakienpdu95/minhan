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
        Schema::create('organization_verticals', function (Blueprint $table) {
            $table->id();
            $table->uuid()->nullable()->unique()->comment('Public UUID — expose ra ngoài, không phải PK');
            $table->unsignedInteger('order_column')->nullable()->index()->comment('Thứ tự sắp xếp — Spatie Sortable / ORDER BY');
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('vertical_code', 50);
            $table->string('status', 20)->default('active');
            $table->json('config')->nullable();
            $table->timestamp('activated_at');
            $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete();
            

            // Indexes
            $table->unique(['organization_id', 'vertical_code']);
            $table->index(['organization_id', 'status']);
        });

        
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_verticals');
    }
};
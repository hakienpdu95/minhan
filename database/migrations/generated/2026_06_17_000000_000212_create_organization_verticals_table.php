<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organization_verticals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->cascadeOnDelete();
            $table->string('vertical_code', 50);
            $table->string('status', 20)->default('active'); // active | suspended
            $table->json('config')->nullable(); // metadata JSON (không phải business data)
            $table->timestamp('activated_at');
            $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->unique(['organization_id', 'vertical_code']);
            $table->index(['organization_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_verticals');
    }
};

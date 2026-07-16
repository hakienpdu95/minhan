<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bảng snapshot bất biến (append-only) — pattern giống sop_versions:
        // chỉ created_at, KHÔNG updated_at/soft delete vì version không bao giờ sửa lại.
        Schema::create('deliverable_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deliverable_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->json('content')->nullable();
            $table->text('change_summary')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['deliverable_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverable_versions');
    }
};

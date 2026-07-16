<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliverables', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->enum('workspace', [
                'context', 'discovery', 'diagnosis', 'transformation',
                'delivery', 'closing', 'knowledge', 'customer_success',
            ])->index();
            // `type` là STRING (không phải DB enum) có chủ đích: sẽ có vài chục giá trị
            // trải qua toàn bộ 8 workspace ở các Phase sau — tránh phải ALTER TABLE MODIFY
            // liên tục như đã gặp với kc_items.type. Validate ở tầng ứng dụng (DeliverableType).
            $table->string('type', 60)->index();
            $table->string('title', 255);
            $table->foreignId('parent_id')->nullable()->constrained('deliverables')->nullOnDelete();
            $table->unsignedInteger('current_version')->default(0);
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'confirmed'])
                ->default('draft')->index();
            $table->timestamp('confirmed_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_project_id', 'workspace']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverables');
    }
};

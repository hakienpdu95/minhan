<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Many-to-many: Diagnosis Matrix trích dẫn Interview/Observation làm evidence, KHÔNG copy
        // nội dung. Chưa có UI dùng tới ở Vertical Slice 1 (chỉ Diagnosis Workspace ở Phase 2 mới
        // thao tác), nhưng thuộc Nền tảng dữ liệu theo spec Phần 6.2 — tạo sẵn để không phải
        // migrate lại khi Phase 2 cần.
        Schema::create('deliverable_evidence_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deliverable_id')->constrained('deliverables')->cascadeOnDelete();
            $table->foreignId('evidence_id')->constrained('deliverables')->cascadeOnDelete();
            $table->enum('evidence_type', [
                'interview', 'observation', 'document_review', 'data_review', 'task', 'metric',
            ])->default('document_review');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['deliverable_id', 'evidence_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverable_evidence_links');
    }
};

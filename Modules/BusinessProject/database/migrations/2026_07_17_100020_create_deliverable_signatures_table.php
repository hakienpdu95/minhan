<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bảng append-only (giống deliverable_versions/business_project_stage_history — KHÔNG
        // sửa/xoá sau khi ghi) — mỗi hàng là 1 lần "Confirmed" (Rule R4) đi kèm chữ ký nội bộ,
        // thay cho việc chỉ lưu confirmed_at/confirmed_by như trước. `provider` cho phép nhiều
        // cơ chế ký cùng tồn tại theo thời gian (đổi provider không làm mất lịch sử chữ ký cũ).
        Schema::create('deliverable_signatures', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('deliverable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deliverable_version_id')->constrained('deliverable_versions')->restrictOnDelete();
            $table->foreignId('signed_by')->constrained('users')->restrictOnDelete();
            $table->string('provider', 40); // 'internal_rsa' hiện tại — xem DeliverableSignatureProvider
            $table->string('algorithm', 40); // VD 'sha256WithRSAEncryption'
            $table->string('content_hash', 64); // sha256 hex của payload đã ký
            $table->text('signature'); // base64
            $table->string('public_key_fingerprint', 64)->nullable();
            $table->timestamp('signed_at');
            $table->timestamps();

            $table->index(['deliverable_id', 'signed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliverable_signatures');
    }
};

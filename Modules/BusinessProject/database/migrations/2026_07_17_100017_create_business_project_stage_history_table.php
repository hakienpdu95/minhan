<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Phần 10 spec — "Average Cycle Time theo giai đoạn": KHÔNG có bảng nào ghi lại
        // business_projects.current_stage đổi lúc nào trước đây (chỉ có current_stage hiện
        // tại) — đúng nguyên tắc spec "nếu 1 KPI cần bảng riêng, nghĩa là mô hình dữ liệu
        // đang thiếu, bổ sung ở NGUỒN" (không phải bảng thống kê song song) — đây là bổ sung
        // ở nguồn (ghi lại mọi lần AdvanceBusinessProjectStageAction chạy), mirror đúng
        // `lead_stage_history` của module Lead. Cycle Time chỉ tính được từ thời điểm bảng
        // này bắt đầu ghi (không backfill được lịch sử trước đó — dữ liệu cũ không tồn tại).
        Schema::create('business_project_stage_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();

            $table->string('stage_from', 30)->nullable();
            $table->string('stage_to', 30);

            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('changed_at');
            $table->timestamp('created_at')->nullable();

            $table->index(['business_project_id', 'changed_at'], 'idx_bp_stage_history_project');
            $table->index(['organization_id', 'changed_at'], 'idx_bp_stage_history_org');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_project_stage_history');
    }
};

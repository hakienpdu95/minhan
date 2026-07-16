<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Template Library (Phase 2, mảng 5/5 — "Template Service" spec Phần 4/9). Mirror đúng
        // pattern org-nullable = global của `lead_sources`/`lead_pipeline_stages`: organization_id
        // NULL = template dùng chung mọi tổ chức (seed sẵn), có giá trị = template riêng của 1 org
        // (tự soạn/chuẩn hóa từ dự án thật, spec Giai đoạn 7 "Template cải tiến -> Template
        // Service"). `type` khớp giá trị DeliverableType (string, không FK cứng — cùng lý do
        // deliverables.type là string, tránh ALTER TABLE khi thêm type mới). `content` là JSON
        // cùng SHAPE với content của DeliverableVersion tương ứng type đó, để prefill trực tiếp
        // vào form không cần transform.
        Schema::create('deliverable_templates', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('organization_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type', 60);
            $table->string('name', 255);
            $table->string('description', 500)->nullable();
            $table->json('content');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'is_active']);
        });

        // Deliverable Engine — ghi nhận deliverable được tạo TỪ template nào (Deliverable Version
        // Discipline KPI ở BCOS Dashboard đang thiếu sub-metric "% dùng Template chuẩn" đúng vì
        // cột này trước đây không tồn tại).
        Schema::table('deliverables', function (Blueprint $table) {
            $table->foreignId('template_id')->nullable()->after('parent_id')
                ->constrained('deliverable_templates')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('deliverables', function (Blueprint $table) {
            $table->dropConstrainedForeignId('template_id');
        });

        Schema::dropIfExists('deliverable_templates');
    }
};

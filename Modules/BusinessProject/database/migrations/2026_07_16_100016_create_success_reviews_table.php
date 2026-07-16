<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Giai đoạn 8 spec — "vòng đời không kết thúc ở Closed": mỗi hàng là 1 lần chạm
        // (touchpoint) của Customer Success với dự án — có thể mang CSAT/NPS (gắn 1
        // survey_response_id đã tồn tại, KHÔNG xây form khảo sát mới — dùng nguyên Survey
        // engine + trang "take" hiện có), hoặc chỉ là 1 lần follow-up/renewal note, hoặc kết
        // quả "New Opportunity" đã chuyển thành Lead (new_lead_id). Không ép mọi touchpoint
        // phải có đủ mọi cột — mỗi lần ghi nhận chỉ điền phần liên quan.
        Schema::create('success_reviews', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();

            // CSAT/NPS — nguồn duy nhất là Survey engine, không nhập tay điểm số. csat_score
            // (1-5) và nps_score (0-10) denormalize từ SurveyAnswer tại thời điểm gắn, để hiển
            // thị nhanh không phải join lại mỗi lần load Workspace.
            $table->foreignId('survey_response_id')->nullable()->constrained('survey_responses')->nullOnDelete();
            $table->unsignedTinyInteger('csat_score')->nullable();
            $table->unsignedTinyInteger('nps_score')->nullable();

            // Follow-up định kỳ
            $table->dateTime('follow_up_at')->nullable();
            $table->text('follow_up_note')->nullable();
            $table->dateTime('followed_up_at')->nullable();

            // Renewal / Long-term Roadmap
            $table->string('renewal_status', 20)->default('none');
            $table->text('renewal_note')->nullable();

            // New Opportunity -> Lead mới (khép vòng lặp toàn hệ thống)
            $table->foreignId('new_lead_id')->nullable()->constrained('leads')->nullOnDelete();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_project_id', 'follow_up_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('success_reviews');
    }
};

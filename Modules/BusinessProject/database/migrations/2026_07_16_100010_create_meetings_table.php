<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // deliverable_id: pattern giống business_contexts.deliverable_id — Meeting là entity
        // cấu trúc (type/held_at), Minutes là nội dung tự do đi qua Deliverable Engine (versioned).
        // Khác business_contexts (1-1 cố định toàn project), ở đây 1-1 nhưng LẶP LẠI nhiều lần
        // (nhiều Meeting/project) — không dùng UpsertSingletonDeliverableAction (khoá theo
        // business_project_id+type, chỉ đúng cho 1 bản/project) mà qua SaveMeetingMinutesAction
        // riêng, khoá theo đúng meeting_id.
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['interview', 'workshop', 'weekly_review', 'retrospective'])->index();
            $table->string('title', 255);
            $table->dateTime('held_at')->nullable();
            $table->foreignId('deliverable_id')->nullable()->constrained('deliverables')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_project_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};

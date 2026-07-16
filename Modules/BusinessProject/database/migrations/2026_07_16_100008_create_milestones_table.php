<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Milestones là bảng riêng (không phải Deliverable) — spec Phần 6.1: mốc theo Roadmap
        // (quick_win/day_30/day_90/day_365), cần query/filter theo category + target_date,
        // khác Interview/Observation (deliverable con, nội dung tự do qua DeliverableVersion).
        Schema::create('milestones', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('organization_id')->constrained()->restrictOnDelete();
            $table->foreignId('business_project_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['quick_win', 'day_30', 'day_90', 'day_365'])->index();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->date('target_date')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['business_project_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('milestones');
    }
};

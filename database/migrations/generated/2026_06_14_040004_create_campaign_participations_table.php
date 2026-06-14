<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_participations', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->notNull()->unique();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('user_id');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->string('status', 20)->default('in_progress')
                ->comment('in_progress | completed | abandoned | declined');

            // Kết quả đánh giá
            $table->decimal('result_tdwcf_score', 5, 2)->nullable();
            $table->string('result_maturity_level', 64)->nullable();
            $table->decimal('result_sandbox_avg', 5, 2)->nullable();

            // Passport entry được tạo từ campaign này
            $table->unsignedBigInteger('passport_entry_id')->nullable();

            // Hành động từ org
            $table->unsignedTinyInteger('org_rating')->nullable()->comment('1–5 sao');
            $table->string('org_note', 500)->nullable();
            $table->string('org_action', 30)->nullable()
                ->comment('shortlisted | invited | hired | rejected');
            $table->timestamp('org_action_at')->nullable();

            $table->timestamps();

            $table->unique(['campaign_id', 'user_id'], 'cp_campaign_user_unique');
            $table->index('user_id', 'cp_user_index');
            $table->index('campaign_id', 'cp_campaign_index');
            $table->index('status', 'cp_status_index');

            $table->foreign('campaign_id', 'cp_campaign_fk')
                ->references('id')->on('open_assessment_campaigns')->cascadeOnDelete();
            $table->foreign('user_id', 'cp_user_fk')
                ->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('passport_entry_id', 'cp_passport_entry_fk')
                ->references('id')->on('passport_entries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_participations');
    }
};

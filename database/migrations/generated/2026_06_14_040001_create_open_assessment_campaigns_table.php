<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('open_assessment_campaigns', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->notNull()->unique();
            $table->unsignedBigInteger('organization_id');
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('target_job_title_id')->nullable();
            $table->string('target_job_title_label', 200)->nullable()
                ->comment('Bất biến kể cả khi job title đổi');
            $table->string('target_department_label', 200)->nullable();

            // Điều kiện tham gia
            $table->unsignedTinyInteger('min_trust_level')->default(2);
            $table->decimal('min_tdwcf_score', 5, 2)->nullable();

            // Cấu hình
            $table->string('status', 20)->default('draft')
                ->comment('draft | open | closed | archived');
            $table->timestamp('open_from')->nullable();
            $table->timestamp('open_until')->nullable();
            $table->unsignedSmallInteger('max_participants')->nullable();
            $table->tinyInteger('is_anonymous_to_org')->default(1)
                ->comment('1=Org không thấy tên cho đến khi invite');

            // Denormalized counters
            $table->unsignedSmallInteger('participants_count')->default(0);
            $table->unsignedSmallInteger('completed_count')->default(0);

            $table->timestamps();

            $table->index('organization_id', 'oac_org_index');
            $table->index('status', 'oac_status_index');
            $table->index('open_until', 'oac_open_until_index');

            $table->foreign('organization_id', 'oac_org_fk')
                ->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('target_job_title_id', 'oac_jobtitle_fk')
                ->references('id')->on('job_titles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('open_assessment_campaigns');
    }
};

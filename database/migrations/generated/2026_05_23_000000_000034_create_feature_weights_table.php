<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_weights', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code', 50)->comment('FK logic tới assessments.assessment_code');
            $table->string('feature_code', 100)->comment('Khớp với score_rules.feature_code hoặc domain_code');
            $table->string('domain_code', 50)->nullable()->comment('NULL nếu weight_level = feature');
            $table->string('weight_level', 20)->default('domain')
                ->comment('domain | feature — mặc định domain (tránh overfit)');

            $table->decimal('weight', 8, 4)->comment('Wi hiện hành');
            $table->decimal('default_weight', 8, 4)->comment('Trọng số gốc (để reset)');
            $table->decimal('weight_min', 8, 4)->default(0)->comment('Giới hạn dưới khi tuning');
            $table->decimal('weight_max', 8, 4)->default(1)->comment('Giới hạn trên khi tuning');

            $table->integer('version')->default(1)->comment('Version tăng mỗi lần cập nhật');
            $table->string('updated_by', 20)->default('manual')
                ->comment('manual | rule_based | ml_model');

            $table->timestamps();

            $table->unique(['assessment_code', 'feature_code'], 'uq_feature_weight');
            $table->index('assessment_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_weights');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recommendation_rules', function (Blueprint $table) {
            $table->unsignedBigInteger('kc_category_id')->nullable()->after('description')
                ->comment('FK tới kc_categories — danh mục tài nguyên học tập');

            $table->string('kc_item_tag', 100)->nullable()->after('kc_category_id')
                ->comment('Tag KcItem: ai_literacy|prompt_engineering|workflow_design|...');

            $table->string('career_pathway_step_code', 50)->nullable()->after('kc_item_tag')
                ->comment('Bước lộ trình nghề nghiệp được đề xuất (from_level của CareerPathwayStep)');
        });
    }

    public function down(): void
    {
        Schema::table('recommendation_rules', function (Blueprint $table) {
            $table->dropColumn(['kc_category_id', 'kc_item_tag', 'career_pathway_step_code']);
        });
    }
};

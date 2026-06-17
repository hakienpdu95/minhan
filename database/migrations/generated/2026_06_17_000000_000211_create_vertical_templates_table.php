<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vertical_templates', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('label', 100);
            $table->string('target_label', 50)->default('Tổ chức');
            $table->string('target_org_category', 30)->default('organization');
            $table->boolean('has_physical_assets')->default(true);
            $table->string('export_adapter', 200)->nullable();
            $table->string('readiness_template_slug', 100)->nullable();
            $table->json('phases');
            $table->json('default_checklist');
            $table->json('default_activity_types')->nullable();
            $table->json('default_legal_doc_types')->nullable();
            $table->json('default_hierarchy');
            $table->json('default_roles');
            $table->json('sidebar_config');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vertical_templates');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vertical_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('vertical_templates', 'data_collection_template_slug')) {
                $table->string('data_collection_template_slug', 100)
                    ->nullable()
                    ->after('readiness_template_slug')
                    ->comment('slug survey template thu thập dữ liệu thực địa — ví dụ: data_collection_v1');
            }
        });

        Schema::table('deployment_targets', function (Blueprint $table) {
            if (! Schema::hasColumn('deployment_targets', 'data_collection_response_id')) {
                $table->foreignId('data_collection_response_id')
                    ->nullable()
                    ->after('readiness_response_id')
                    ->constrained('survey_responses')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('deployment_targets', function (Blueprint $table) {
            if (Schema::hasColumn('deployment_targets', 'data_collection_response_id')) {
                $table->dropForeign(['data_collection_response_id']);
                $table->dropColumn('data_collection_response_id');
            }
        });

        Schema::table('vertical_templates', function (Blueprint $table) {
            if (Schema::hasColumn('vertical_templates', 'data_collection_template_slug')) {
                $table->dropColumn('data_collection_template_slug');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deployment_targets', function (Blueprint $table) {
            if (!Schema::hasColumn('deployment_targets', 'readiness_response_id')) {
                $table->foreignId('readiness_response_id')->nullable()->constrained('survey_responses')->nullOnDelete();
            }
            if (!Schema::hasColumn('deployment_targets', 'readiness_score')) {
                $table->unsignedTinyInteger('readiness_score')->nullable()->after('readiness_response_id')->comment('0-100 readiness score; null = not assessed yet');
            }
            if (!Schema::hasColumn('deployment_targets', 'data_collection_response_id')) {
                $table->foreignId('data_collection_response_id')->nullable()->constrained('survey_responses')->nullOnDelete()->after('readiness_score');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deployment_targets', function (Blueprint $table) {
            if (Schema::hasColumn('deployment_targets', 'readiness_response_id')) $table->dropForeign(['readiness_response_id']);
            if (Schema::hasColumn('deployment_targets', 'data_collection_response_id')) $table->dropForeign(['data_collection_response_id']);
            $cols = array_filter(['readiness_response_id', 'readiness_score', 'data_collection_response_id'], fn($c) => Schema::hasColumn('deployment_targets', $c));
            if (!empty($cols)) $table->dropColumn(array_values($cols));
        });
    }
};
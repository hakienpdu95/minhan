<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deployment_targets', function (Blueprint $table) {
            if (! Schema::hasColumn('deployment_targets', 'readiness_response_id')) {
                $table->foreignId('readiness_response_id')
                    ->nullable()
                    ->after('notes')
                    ->constrained('survey_responses')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('deployment_targets', 'readiness_score')) {
                $table->unsignedTinyInteger('readiness_score')
                    ->nullable()
                    ->after('readiness_response_id')
                    ->comment('0-100 readiness score; null = not assessed yet');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deployment_targets', function (Blueprint $table) {
            $cols = array_filter(['readiness_response_id', 'readiness_score'],
                fn($c) => Schema::hasColumn('deployment_targets', $c));
            if (! empty($cols)) {
                $table->dropForeign(['readiness_response_id']);
                $table->dropColumn(array_values($cols));
            }
        });
    }
};

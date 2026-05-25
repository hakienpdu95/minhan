<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Composite index for dedup queries: check before adding to be safe
        $hasIdx = DB::select(
            "SELECT 1 FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = 'survey_responses'
               AND index_name = 'survey_responses_survey_respondent_idx'
             LIMIT 1"
        );
        if (empty($hasIdx)) {
            Schema::table('survey_responses', function (Blueprint $table) {
                $table->index(['survey_id', 'respondent_ref'], 'survey_responses_survey_respondent_idx');
            });
        }

        // Composite index for behavior log queries by response + time range
        Schema::table('submission_behavior_log', function (Blueprint $table) {
            $table->index(['response_id', 'occurred_at'], 'behavior_logs_response_occurred_idx');
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropIndexIfExists('survey_responses_survey_respondent_idx');
        });

        Schema::table('submission_behavior_log', function (Blueprint $table) {
            $table->dropIndexIfExists('behavior_logs_response_occurred_idx');
        });
    }
};

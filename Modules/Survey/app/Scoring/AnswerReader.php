<?php

namespace Modules\Survey\Scoring;

use Illuminate\Support\Facades\DB;

class AnswerReader
{
    /**
     * Load tất cả câu trả lời của một response, map về field_key.
     *
     * Trả về: ['field_key' => AnswerPayload]
     * - boolean: ['type' => 'boolean', 'value' => true|false]
     * - single_choice: ['type' => 'single_choice', 'values' => ['option_value']]
     * - multi_choice: ['type' => 'multi_choice', 'values' => ['opt_a', 'opt_b']]
     * - other: ['type' => 'other', 'value' => 'raw_string']
     *
     * @return array<string, array>
     */
    public function read(int $responseId, int $surveyId): array
    {
        // Một query duy nhất — join survey_answers → survey_fields → survey_field_options
        $rows = DB::table('survey_answers as sa')
            ->join('survey_fields as sf', 'sf.id', '=', 'sa.field_id')
            ->leftJoin('survey_field_options as sfo', 'sfo.id', '=', 'sa.option_id')
            ->where('sa.response_id', $responseId)
            ->where('sf.survey_id', $surveyId)
            ->select([
                'sf.field_key',
                'sf.field_type',
                'sa.value_bool',
                'sa.value_number',
                'sa.value_string',
                'sfo.option_value',
                'sfo.is_other',
            ])
            ->get();

        // Group theo field_key, build payload
        $result = [];

        foreach ($rows as $row) {
            $key = $row->field_key;

            if ($row->option_value !== null) {
                // Choice field (single hoặc multi — phân biệt ở RuleEngine)
                $result[$key]['type']     = 'choice';
                $result[$key]['values'][] = $row->option_value;
            } elseif ($row->value_bool !== null) {
                $result[$key] = ['type' => 'boolean', 'value' => (bool) $row->value_bool];
            } elseif ($row->value_number !== null) {
                $result[$key] = ['type' => 'number', 'value' => (float) $row->value_number];
            } elseif ($row->value_string !== null) {
                $result[$key] = ['type' => 'string', 'value' => $row->value_string];
            }
        }

        return $result;
    }

    /**
     * Compute per-question behavior metrics from submission_behavior_log.
     *
     * Returns: ['field_key' => ['time_spent_seconds' => float, 'change_count' => int, 'hesitation_index' => int]]
     *   time_spent_seconds — sum of all time_spent event values
     *   change_count       — number of answer_changed events
     *   hesitation_index   — number of answer_changed + answer_cleared events combined
     *
     * @return array<string, array{time_spent_seconds: float, change_count: int, hesitation_index: int}>
     */
    public function readBehavior(int $responseId): array
    {
        $rows = DB::table('submission_behavior_log')
            ->where('response_id', $responseId)
            ->whereNotNull('question_code')
            ->whereIn('event_type', ['time_spent', 'answer_changed', 'answer_cleared'])
            ->select(['question_code', 'event_type', 'event_value'])
            ->get();

        $metrics = [];

        foreach ($rows as $row) {
            $code = $row->question_code;

            if (!isset($metrics[$code])) {
                $metrics[$code] = [
                    'time_spent_seconds' => 0.0,
                    'change_count'       => 0,
                    'hesitation_index'   => 0,
                ];
            }

            if ($row->event_type === 'time_spent') {
                $metrics[$code]['time_spent_seconds'] += (float) ($row->event_value ?? 0);
            } elseif ($row->event_type === 'answer_changed') {
                $metrics[$code]['change_count']++;
                $metrics[$code]['hesitation_index']++;
            } elseif ($row->event_type === 'answer_cleared') {
                $metrics[$code]['hesitation_index']++;
            }
        }

        return $metrics;
    }
}

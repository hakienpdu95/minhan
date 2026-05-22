<?php

namespace Modules\Survey\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\Survey\Enums\FieldType;
use Modules\Survey\Enums\ResponseStatus;
use Modules\Survey\Models\Survey;
use Modules\Survey\Models\SurveyField;
use Modules\Survey\Models\SurveyResponse;

/**
 * Read-only aggregate service (CQRS-lite: tách hoàn toàn khỏi write path).
 *
 * Mọi query ĐỀU index-backed — không có full table scan:
 *   choice   → INDEX (field_id, option_id)
 *   number   → INDEX (field_id, value_number)
 *   boolean  → INDEX (field_id, value_bool)
 *   text     → INDEX (field_id, value_string)
 *   textarea → INDEX (field_id, option_id) via field_id prefix
 *
 * Pattern: batch query per stat type → O(5) queries thay vì O(N fields).
 */
class SurveyStatsService
{
    public const CACHE_TTL = 300; // 5 phút

    public static function cacheKey(int $surveyId): string
    {
        return "survey:stats:{$surveyId}";
    }

    public static function purgeCache(int $surveyId): void
    {
        Cache::store('redis')->forget(static::cacheKey($surveyId));
    }

    // ── Public API ────────────────────────────────────────────────────────

    /**
     * Trả toàn bộ thống kê của một survey — có cache Redis 5 phút.
     *
     * @return array{
     *   total_responses: int,
     *   fields: array<int, array{field_key: string, label: string, field_type: string, stats: array}>
     * }
     */
    public function forSurvey(Survey $survey): array
    {
        return Cache::store('redis')->remember(
            static::cacheKey($survey->id),
            static::CACHE_TTL,
            fn () => $this->computeForSurvey($survey)
        );
    }

    private function computeForSurvey(Survey $survey): array
    {
        $totalResponses = $this->countCompleteResponses($survey->id);

        // Load tất cả active fields + options (choice fields cần options để map label)
        $fields = SurveyField::forSurvey($survey->id)
            ->active()
            ->with(['options' => fn ($q) => $q->ordered()])
            ->ordered()
            ->get();

        // Nhóm field_id theo stat type để batch query
        $choiceIds   = $fields->filter(fn ($f) => $f->field_type->isChoice())->pluck('id')->all();
        $numberIds   = $fields->filter(fn ($f) => $this->isNumberType($f))->pluck('id')->all();
        $booleanIds  = $fields->filter(fn ($f) => $f->field_type === FieldType::Boolean)->pluck('id')->all();
        $textIds     = $fields->filter(fn ($f) => $f->field_type === FieldType::Text)->pluck('id')->all();
        $textareaIds = $fields->filter(fn ($f) => $f->field_type === FieldType::Textarea)->pluck('id')->all();

        // 5 batch queries — một lần mỗi loại, tất cả index-backed
        $choiceStats   = $this->batchChoiceStats($choiceIds, $fields, $totalResponses);
        $numberStats   = $this->batchNumberStats($numberIds);
        $booleanStats  = $this->batchBooleanStats($booleanIds);
        $textStats     = $this->batchTextStats($textIds);
        $textareaStats = $this->batchTextareaStats($textareaIds);

        $allStats = $choiceStats + $numberStats + $booleanStats + $textStats + $textareaStats;

        return [
            'total_responses' => $totalResponses,
            'fields'          => $fields
                ->map(fn ($f) => [
                    'field_key'  => $f->field_key,
                    'label'      => $f->label,
                    'field_type' => $f->field_type->name,
                    'stats'      => $allStats[$f->id] ?? null,
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * Chỉ đếm responses hoàn chỉnh (status = complete).
     * Dùng INDEX (survey_id, status, submitted_at).
     */
    public function countCompleteResponses(int $surveyId): int
    {
        return SurveyResponse::forSurvey($surveyId)
            ->complete()
            ->count();
    }

    // ── Batch: choice (select / radio / checkbox) ─────────────────────────
    //   INDEX: (field_id, option_id)

    /**
     * @param  int[]     $fieldIds
     * @param  Collection<int, SurveyField> $allFields  — options đã eager load
     * @return array<int, array>  keyed by field_id
     */
    private function batchChoiceStats(array $fieldIds, Collection $allFields, int $totalResponses): array
    {
        if (empty($fieldIds)) {
            return [];
        }

        // GROUP BY (field_id, option_id) — hits (field_id, option_id) covering index
        $counts = DB::table('survey_answers')
            ->selectRaw('field_id, option_id, COUNT(*) as cnt')
            ->whereIn('field_id', $fieldIds)
            ->whereNotNull('option_id')
            ->groupBy('field_id', 'option_id')
            ->get()
            ->groupBy('field_id');             // Collection<field_id, Collection<{option_id, cnt}>>

        $choiceFields = $allFields->whereIn('id', $fieldIds)->keyBy('id');

        $result = [];

        foreach ($fieldIds as $fieldId) {
            $field        = $choiceFields[$fieldId];
            $fieldCounts  = $counts->get($fieldId, collect())->keyBy('option_id');

            $distribution = $field->options->map(function ($option) use ($fieldCounts, $totalResponses) {
                $count = (int) ($fieldCounts->get($option->id)?->cnt ?? 0);

                return [
                    'option_value' => $option->option_value,
                    'label'        => $option->label,
                    'count'        => $count,
                    'percent'      => $totalResponses > 0
                        ? round($count / $totalResponses * 100, 1)
                        : 0.0,
                ];
            })->values()->all();

            $result[$fieldId] = [
                'type'         => 'choice',
                'distribution' => $distribution,
            ];
        }

        return $result;
    }

    // ── Batch: number / rating ────────────────────────────────────────────
    //   INDEX: (field_id, value_number)

    /**
     * @param  int[]  $fieldIds
     * @return array<int, array>  keyed by field_id
     */
    private function batchNumberStats(array $fieldIds): array
    {
        if (empty($fieldIds)) {
            return [];
        }

        $rows = DB::table('survey_answers')
            ->selectRaw('field_id,
                COUNT(*)            AS cnt,
                AVG(value_number)   AS avg_val,
                MIN(value_number)   AS min_val,
                MAX(value_number)   AS max_val')
            ->whereIn('field_id', $fieldIds)
            ->whereNotNull('value_number')   // WHERE field_id IN (...) → uses index prefix
            ->groupBy('field_id')
            ->get()
            ->keyBy('field_id');

        $result = [];

        foreach ($fieldIds as $fieldId) {
            $row = $rows->get($fieldId);

            $result[$fieldId] = [
                'type'  => 'number',
                'count' => (int)   ($row?->cnt     ?? 0),
                'avg'   => $row?->avg_val !== null ? round((float) $row->avg_val, 2) : null,
                'min'   => $row?->min_val !== null ? (float) $row->min_val          : null,
                'max'   => $row?->max_val !== null ? (float) $row->max_val          : null,
            ];
        }

        return $result;
    }

    // ── Batch: boolean ────────────────────────────────────────────────────
    //   INDEX: (field_id, value_bool)

    /**
     * @param  int[]  $fieldIds
     * @return array<int, array>  keyed by field_id
     */
    private function batchBooleanStats(array $fieldIds): array
    {
        if (empty($fieldIds)) {
            return [];
        }

        // GROUP BY (field_id, value_bool) — 2 rows per field (true / false)
        $rows = DB::table('survey_answers')
            ->selectRaw('field_id, value_bool, COUNT(*) AS cnt')
            ->whereIn('field_id', $fieldIds)
            ->whereNotNull('value_bool')
            ->groupBy('field_id', 'value_bool')
            ->get()
            ->groupBy('field_id');

        $result = [];

        foreach ($fieldIds as $fieldId) {
            $fieldRows = $rows->get($fieldId, collect());

            // MySQL lưu TINYINT 1/0 — cast sang int khi so sánh
            $yesCount = (int) ($fieldRows->first(fn ($r) => (int) $r->value_bool === 1)?->cnt ?? 0);
            $noCount  = (int) ($fieldRows->first(fn ($r) => (int) $r->value_bool === 0)?->cnt ?? 0);

            $result[$fieldId] = [
                'type'      => 'boolean',
                'yes_count' => $yesCount,
                'no_count'  => $noCount,
                'total'     => $yesCount + $noCount,
            ];
        }

        return $result;
    }

    // ── Batch: text (value_string) ────────────────────────────────────────
    //   INDEX: (field_id, value_string)  — covering index cho COUNT

    /**
     * @param  int[]  $fieldIds
     * @return array<int, array>  keyed by field_id
     */
    private function batchTextStats(array $fieldIds): array
    {
        if (empty($fieldIds)) {
            return [];
        }

        $rows = DB::table('survey_answers')
            ->selectRaw('field_id, COUNT(*) AS cnt')
            ->whereIn('field_id', $fieldIds)
            ->whereNotNull('value_string')   // (field_id, value_string) index
            ->groupBy('field_id')
            ->get()
            ->keyBy('field_id');

        return $this->buildTextResult($fieldIds, $rows);
    }

    // ── Batch: textarea (value_text) ──────────────────────────────────────
    //   value_text không có index riêng — dùng field_id prefix của index
    //   (field_id, option_id) để scan theo field, filter value_text IS NOT NULL

    /**
     * @param  int[]  $fieldIds
     * @return array<int, array>  keyed by field_id
     */
    private function batchTextareaStats(array $fieldIds): array
    {
        if (empty($fieldIds)) {
            return [];
        }

        // Tất cả answer cho textarea field đều có value_text (không NULL).
        // COUNT(*) WHERE field_id IN (...) dùng prefix của (field_id, option_id) index.
        $rows = DB::table('survey_answers')
            ->selectRaw('field_id, COUNT(*) AS cnt')
            ->whereIn('field_id', $fieldIds)
            ->groupBy('field_id')
            ->get()
            ->keyBy('field_id');

        return $this->buildTextResult($fieldIds, $rows);
    }

    // ── Task 4.4: Total responses per day ─────────────────────────────────
    //   INDEX: (survey_id, status, submitted_at) — prefix scan → DATE() → GROUP BY

    /**
     * Trả mảng {day, count} liên tục $days ngày gần nhất (điền 0 cho ngày trống).
     *
     * @return array<int, array{day: string, count: int}>
     */
    public function totalByDay(Survey $survey, int $days = 30): array
    {
        $from = now()->subDays($days - 1)->startOfDay();

        // Dùng Eloquent để SoftDeletes global scope tự động loại trừ deleted_at IS NOT NULL
        $rows = SurveyResponse::forSurvey($survey->id)
            ->complete()
            ->where('submitted_at', '>=', $from)
            ->selectRaw('DATE(submitted_at) as day, COUNT(*) as cnt')
            ->groupByRaw('DATE(submitted_at)')
            ->orderBy('day')
            ->get()
            ->keyBy('day');

        // Điền ngày trống với count = 0 để biểu đồ liên tục
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $day      = now()->subDays($i)->format('Y-m-d');
            $result[] = ['day' => $day, 'count' => (int) ($rows->get($day)?->cnt ?? 0)];
        }

        return $result;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function buildTextResult(array $fieldIds, Collection $rows): array
    {
        $result = [];

        foreach ($fieldIds as $fieldId) {
            $result[$fieldId] = [
                'type'  => 'text',
                'count' => (int) ($rows->get($fieldId)?->cnt ?? 0),
            ];
        }

        return $result;
    }

    private function isNumberType(SurveyField $field): bool
    {
        return $field->field_type === FieldType::Number
            || $field->field_type === FieldType::Rating;
    }
}

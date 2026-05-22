<?php

namespace Modules\Survey\Services;

use Illuminate\Support\Collection;
use Modules\Survey\Enums\FieldType;
use Modules\Survey\Models\SurveyAnswer;
use Modules\Survey\Models\SurveyField;
use Modules\Survey\Models\SurveyResponse;
use Modules\Survey\Models\SurveySection;

/**
 * Dựng lại 1 response thành cấu trúc đọc được: section → field → answer.
 *
 * Query plan: 3 queries cố định, không N+1.
 *   Q1: sections (by survey_id, ordered)
 *   Q2: fields + options (eager load, by section_id IN ...)
 *   Q3: answers + options (by response_id, eager load option)
 */
class ResponseViewerService
{
    public function build(SurveyResponse $response): array
    {
        // Q1 + Q2: survey sections → fields (all, kể cả inactive để hiện lịch sử) → options
        $sections = SurveySection::where('survey_id', $response->survey_id)
            ->ordered()
            ->with(['fields' => fn ($q) => $q->ordered()->with('options')])
            ->get();

        // Q3: tất cả answers của response này, group theo field_id
        $answers = SurveyAnswer::where('response_id', $response->id)
            ->with('option')
            ->get()
            ->groupBy('field_id');

        $built = $sections->map(fn ($section) => [
            'title'  => $section->title,
            'icon'   => $section->icon,
            'fields' => $section->fields
                ->map(fn ($field) => $this->buildField($field, $answers->get($field->id, collect())))
                ->all(),
        ])->all();

        return [
            'response' => $response,
            'sections' => $built,
        ];
    }

    // ── Private ───────────────────────────────────────────────────────────

    private function buildField(SurveyField $field, Collection $answers): array
    {
        $isAnswered = $answers->isNotEmpty();

        return [
            'label'       => $field->label,
            'field_key'   => $field->field_key,
            'field_type'  => $field->field_type,
            'is_active'   => $field->is_active,
            'is_required' => $field->is_required,
            'is_answered' => $isAnswered,
            'is_multiple' => $field->field_type->allowsMultiple(),
            'display'     => $isAnswered ? $this->formatDisplay($field, $answers) : null,
        ];
    }

    private function formatDisplay(SurveyField $field, Collection $answers): string|array|null
    {
        return match ($field->field_type) {
            FieldType::Text     => $answers->first()->value_string,
            FieldType::Textarea => $answers->first()->value_text,
            FieldType::Number,
            FieldType::Rating   => (string) ($answers->first()->value_number ?? ''),
            FieldType::Date     => $answers->first()->value_date?->format('d/m/Y'),
            FieldType::Boolean  => $answers->first()->value_bool ? 'Có' : 'Không',
            FieldType::Select,
            FieldType::Radio    => $answers->first()->option?->label,
            FieldType::Checkbox => $answers->map(fn ($a) => $a->option?->label)->filter()->values()->all(),
            default             => null,
        };
    }
}

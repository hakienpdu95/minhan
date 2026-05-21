<?php

namespace Modules\Survey\Actions;

use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Survey\Data\SurveySchemaData;
use Modules\Survey\Enums\SurveyStatus;
use Modules\Survey\Models\Survey;

/**
 * Lấy definition đầy đủ của một khảo sát đang active.
 *
 * Eager load sections → fields (active only) → options trong một query chain
 * để tránh N+1. Không chứa business logic — chỉ đọc và map sang DTO.
 */
class BuildSurveySchemaAction
{
    use AsAction;

    public function handle(string $slug): SurveySchemaData
    {
        $survey = Survey::active()
            ->bySlug($slug)
            ->with([
                'sections' => fn ($q) => $q->ordered(),
                'sections.fields' => fn ($q) => $q->active()->ordered(),
                'sections.fields.options' => fn ($q) => $q->ordered(),
            ])
            ->firstOrFail();

        return SurveySchemaData::fromModel($survey);
    }
}

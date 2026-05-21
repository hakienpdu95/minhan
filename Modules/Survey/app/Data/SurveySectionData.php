<?php

namespace Modules\Survey\Data;

use Modules\Survey\Models\SurveySection;
use Spatie\LaravelData\Data;

class SurveySectionData extends Data
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $title,
        public readonly ?string $icon,
        public readonly int     $sort_order,
        /** @var SurveyFieldData[] */
        public readonly array   $fields,
    ) {}

    public static function fromModel(SurveySection $section): self
    {
        return new self(
            id:         $section->id,
            title:      $section->title,
            icon:       $section->icon,
            sort_order: $section->sort_order,
            fields:     $section->fields
                ->map(fn($f) => SurveyFieldData::fromModel($f))
                ->all(),
        );
    }
}

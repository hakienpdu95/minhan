<?php

namespace Modules\Survey\Data;

use Modules\Survey\Models\Survey;
use Spatie\LaravelData\Data;

class SurveySchemaData extends Data
{
    public function __construct(
        public readonly int    $id,
        public readonly string $title,
        public readonly string $slug,
        public readonly int    $version,
        /** @var SurveySectionData[] */
        public readonly array  $sections,
    ) {}

    public static function fromModel(Survey $survey): self
    {
        return new self(
            id:       $survey->id,
            title:    $survey->title,
            slug:     $survey->slug,
            version:  $survey->version,
            sections: $survey->sections
                ->map(fn($s) => SurveySectionData::fromModel($s))
                ->all(),
        );
    }
}

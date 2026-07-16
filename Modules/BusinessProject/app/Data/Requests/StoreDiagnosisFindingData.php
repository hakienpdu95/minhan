<?php

namespace Modules\BusinessProject\Data\Requests;

use Illuminate\Validation\Rule;
use Modules\BusinessProject\Enums\DiagnosisCategory;
use Modules\BusinessProject\Enums\DiagnosisEffort;
use Modules\BusinessProject\Enums\DiagnosisImpact;
use Spatie\LaravelData\Data;

class StoreDiagnosisFindingData extends Data
{
    public function __construct(
        public readonly string $problem,
        public readonly string $category,
        public readonly ?string $root_cause,
        public readonly string $impact,
        public readonly string $effort,
    ) {}

    public static function rules(): array
    {
        return [
            'problem' => ['required', 'string', 'max:500'],
            'category' => ['required', 'string', Rule::in(array_column(DiagnosisCategory::cases(), 'value'))],
            'root_cause' => ['nullable', 'string'],
            'impact' => ['required', 'string', Rule::in(array_column(DiagnosisImpact::cases(), 'value'))],
            'effort' => ['required', 'string', Rule::in(array_column(DiagnosisEffort::cases(), 'value'))],
        ];
    }
}

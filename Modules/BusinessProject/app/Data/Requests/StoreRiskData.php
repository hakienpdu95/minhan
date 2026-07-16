<?php

namespace Modules\BusinessProject\Data\Requests;

use Illuminate\Validation\Rule;
use Modules\BusinessProject\Enums\RiskImpact;
use Modules\BusinessProject\Enums\RiskLikelihood;
use Spatie\LaravelData\Data;

class StoreRiskData extends Data
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $likelihood,
        public readonly string $impact,
    ) {}

    public static function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'likelihood' => ['required', 'string', Rule::in(array_column(RiskLikelihood::cases(), 'value'))],
            'impact' => ['required', 'string', Rule::in(array_column(RiskImpact::cases(), 'value'))],
        ];
    }
}

<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class CreateLeadFromOpportunityData extends Data
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?float $expected_value = null,
    ) {}

    public static function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:5000'],
            'expected_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}

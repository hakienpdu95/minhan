<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class StoreBusinessContextData extends Data
{
    public function __construct(
        public readonly ?array $company_profile,
        public readonly ?array $stakeholders,
        public readonly ?array $strategic_goals,
    ) {}

    public static function rules(): array
    {
        return [
            'company_profile' => ['nullable', 'array'],
            'stakeholders' => ['nullable', 'array'],
            'strategic_goals' => ['nullable', 'array'],
        ];
    }

    public static function messages(): array
    {
        return [
            'company_profile.array' => 'Company Profile không đúng định dạng.',
            'stakeholders.array' => 'Stakeholder Map không đúng định dạng.',
            'strategic_goals.array' => 'Strategic Goals không đúng định dạng.',
        ];
    }
}

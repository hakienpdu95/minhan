<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class StoreDiagnosisOverviewData extends Data
{
    public function __construct(
        public readonly ?string $overview,
        public readonly ?int $template_id = null,
    ) {}

    public static function rules(): array
    {
        return [
            'overview' => ['nullable', 'string'],
            'template_id' => ['nullable', 'integer', 'exists:deliverable_templates,id'],
        ];
    }
}

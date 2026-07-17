<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class StoreTpsCanvasData extends Data
{
    public function __construct(
        public readonly ?string $problem,
        public readonly ?string $goal,
        public readonly ?string $scope,
        public readonly ?int $template_id = null,
    ) {}

    public static function rules(): array
    {
        return [
            'problem' => ['nullable', 'string'],
            'goal' => ['nullable', 'string'],
            'scope' => ['nullable', 'string'],
            'template_id' => ['nullable', 'integer', 'exists:deliverable_templates,id'],
        ];
    }
}

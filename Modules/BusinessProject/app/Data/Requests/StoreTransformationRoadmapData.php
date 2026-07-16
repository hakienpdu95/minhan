<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class StoreTransformationRoadmapData extends Data
{
    public function __construct(
        public readonly ?string $overview,
    ) {}

    public static function rules(): array
    {
        return [
            'overview' => ['nullable', 'string'],
        ];
    }
}

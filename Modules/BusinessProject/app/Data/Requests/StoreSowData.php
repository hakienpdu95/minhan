<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class StoreSowData extends Data
{
    public function __construct(
        public readonly ?string $scope,
        public readonly ?string $deliverables,
        public readonly ?string $responsibilities,
    ) {}

    public static function rules(): array
    {
        return [
            'scope' => ['nullable', 'string'],
            'deliverables' => ['nullable', 'string'],
            'responsibilities' => ['nullable', 'string'],
        ];
    }
}

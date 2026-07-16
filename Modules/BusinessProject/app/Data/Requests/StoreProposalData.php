<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class StoreProposalData extends Data
{
    public function __construct(
        public readonly ?string $solution,
        public readonly ?string $collaboration_plan,
    ) {}

    public static function rules(): array
    {
        return [
            'solution' => ['nullable', 'string'],
            'collaboration_plan' => ['nullable', 'string'],
        ];
    }
}

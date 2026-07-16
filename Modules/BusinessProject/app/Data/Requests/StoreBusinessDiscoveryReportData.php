<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class StoreBusinessDiscoveryReportData extends Data
{
    public function __construct(
        public readonly ?string $summary,
    ) {}

    public static function rules(): array
    {
        return [
            'summary' => ['nullable', 'string'],
        ];
    }
}

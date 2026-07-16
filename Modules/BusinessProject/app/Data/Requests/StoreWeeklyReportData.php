<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class StoreWeeklyReportData extends Data
{
    public function __construct(
        public readonly ?string $narrative,
    ) {}

    public static function rules(): array
    {
        return [
            'narrative' => ['nullable', 'string'],
        ];
    }
}

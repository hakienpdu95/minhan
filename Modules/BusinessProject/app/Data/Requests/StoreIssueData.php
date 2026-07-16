<?php

namespace Modules\BusinessProject\Data\Requests;

use Illuminate\Validation\Rule;
use Modules\BusinessProject\Enums\IssueSeverity;
use Spatie\LaravelData\Data;

class StoreIssueData extends Data
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly string $severity,
    ) {}

    public static function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'severity' => ['required', 'string', Rule::in(array_column(IssueSeverity::cases(), 'value'))],
        ];
    }
}

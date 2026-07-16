<?php

namespace Modules\BusinessProject\Data\Requests;

use Illuminate\Validation\Rule;
use Modules\BusinessProject\Enums\MilestoneCategory;
use Spatie\LaravelData\Data;

class StoreMilestoneData extends Data
{
    public function __construct(
        public readonly string $category,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $target_date,
    ) {}

    public static function rules(): array
    {
        return [
            'category' => ['required', 'string', Rule::in(array_map(fn (MilestoneCategory $c) => $c->value, MilestoneCategory::ordered()))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_date' => ['nullable', 'date'],
        ];
    }
}

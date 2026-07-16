<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class StoreChangeRequestData extends Data
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $description,
        public readonly bool $impacts_scope,
    ) {}

    public static function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'impacts_scope' => ['nullable', 'boolean'],
        ];
    }
}

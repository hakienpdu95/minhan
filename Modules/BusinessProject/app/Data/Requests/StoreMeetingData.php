<?php

namespace Modules\BusinessProject\Data\Requests;

use Illuminate\Validation\Rule;
use Modules\BusinessProject\Enums\MeetingType;
use Spatie\LaravelData\Data;

class StoreMeetingData extends Data
{
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $held_at,
    ) {}

    public static function rules(): array
    {
        return [
            'type' => ['required', 'string', Rule::in(array_column(MeetingType::cases(), 'value'))],
            'title' => ['required', 'string', 'max:255'],
            'held_at' => ['nullable', 'date'],
        ];
    }
}

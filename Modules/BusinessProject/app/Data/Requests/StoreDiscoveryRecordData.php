<?php

namespace Modules\BusinessProject\Data\Requests;

use Illuminate\Validation\Rule;
use Modules\BusinessProject\Enums\DeliverableType;
use Spatie\LaravelData\Data;

class StoreDiscoveryRecordData extends Data
{
    public function __construct(
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $notes,
        public readonly ?string $occurred_at,
        public readonly ?string $participants,
    ) {}

    public static function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::in(array_map(fn (DeliverableType $t) => $t->value, DeliverableType::discoveryRecordTypes())),
            ],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'occurred_at' => ['nullable', 'date'],
            'participants' => ['nullable', 'string', 'max:500'],
        ];
    }

    public static function messages(): array
    {
        return [
            'type.in' => 'Loại bản ghi không hợp lệ.',
            'title.required' => 'Vui lòng nhập tiêu đề bản ghi.',
        ];
    }
}

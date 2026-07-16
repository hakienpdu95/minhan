<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class SaveMeetingMinutesData extends Data
{
    public function __construct(
        public readonly ?string $minutes,
        public readonly ?string $action_items,
    ) {}

    public static function rules(): array
    {
        return [
            'minutes' => ['nullable', 'string'],
            // Textarea 1 dòng/action item — tách dòng ở Action, không ép cấu trúc form phức tạp.
            'action_items' => ['nullable', 'string'],
        ];
    }
}

<?php

namespace Modules\BusinessProject\Data\Requests;

use Spatie\LaravelData\Data;

class AttachKnowledgeAssetData extends Data
{
    public function __construct(
        public readonly int $kc_item_id,
    ) {}

    public static function rules(): array
    {
        return [
            'kc_item_id' => ['required', 'integer', 'exists:kc_items,id'],
        ];
    }
}

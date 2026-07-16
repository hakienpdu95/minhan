<?php

namespace Modules\BusinessProject\Data\Requests;

use Illuminate\Validation\Rule;
use Modules\BusinessProject\Enums\RenewalStatus;
use Spatie\LaravelData\Data;

/**
 * Giai đoạn 8 — 1 hàng touchpoint Customer Success KHÔNG gắn khảo sát: follow-up định kỳ
 * và/hoặc renewal. Cho phép điền độc lập từng phần (VD chỉ ghi follow-up, chưa có renewal).
 */
class StoreSuccessReviewNoteData extends Data
{
    public function __construct(
        public readonly ?string $follow_up_at = null,
        public readonly ?string $follow_up_note = null,
        public readonly ?string $renewal_status = null,
        public readonly ?string $renewal_note = null,
    ) {}

    public static function rules(): array
    {
        return [
            'follow_up_at' => ['nullable', 'date'],
            'follow_up_note' => ['nullable', 'string', 'max:2000'],
            'renewal_status' => ['nullable', 'string', Rule::in(array_column(RenewalStatus::cases(), 'value'))],
            'renewal_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}

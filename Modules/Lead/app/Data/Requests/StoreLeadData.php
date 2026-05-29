<?php

namespace Modules\Lead\Data\Requests;

use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Integer;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Min;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Numeric;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Data;

class StoreLeadData extends Data
{
    public function __construct(
        #[Required, StringType, Max(200)]
        public readonly string  $contact_name,

        #[Nullable, StringType, Max(30)]
        public readonly ?string $contact_phone        = null,

        #[Nullable, Email, Max(200)]
        public readonly ?string $contact_email        = null,

        #[Nullable, StringType, Max(200)]
        public readonly ?string $contact_company      = null,

        #[Required, Integer, Min(1)]
        public readonly int     $stage_id             = 0,

        #[Nullable, Integer, Min(1)]
        public readonly ?int    $source_id            = null,

        #[Nullable, StringType, Max(500)]
        public readonly ?string $source_detail        = null,

        #[Nullable, Integer, Min(1)]
        public readonly ?int    $assigned_to          = null,

        #[Nullable, Numeric, Min(0)]
        public readonly ?float  $expected_value       = null,

        #[Nullable, StringType, Max(3)]
        public readonly string  $currency             = 'VND',

        #[Nullable, Date]
        public readonly ?string $expected_close_date  = null,

        #[Nullable, StringType, Max(500)]
        public readonly ?string $title                = null,

        #[Nullable, StringType, Max(5000)]
        public readonly ?string $description          = null,
    ) {}

    /**
     * Dedup hash for contact matching — based on email or phone digits.
     */
    public function contactDedupHash(): ?string
    {
        $email = strtolower(trim($this->contact_email ?? ''));
        $phone = preg_replace('/\D/', '', $this->contact_phone ?? '');
        $key   = $email ?: $phone;

        return $key ? md5($key) : null;
    }
}

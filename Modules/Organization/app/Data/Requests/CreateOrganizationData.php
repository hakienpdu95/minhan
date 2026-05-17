<?php

namespace Modules\Organization\Data\Requests;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Url;
use Spatie\LaravelData\Data;

class CreateOrganizationData extends Data
{
    public function __construct(
        #[Required, Max(255)]
        public readonly string $name,

        #[Nullable, Max(20)]
        public readonly ?string $tax_code,

        #[Nullable, Max(20)]
        public readonly ?string $phone,

        #[Nullable, Email, Max(255)]
        public readonly ?string $email,

        #[Nullable, Url, Max(255)]
        public readonly ?string $website,

        #[Nullable, Max(100)]
        public readonly ?string $industry,

        #[Nullable, Max(500)]
        public readonly ?string $address,

        #[Nullable, Max(100)]
        public readonly ?string $city,

        #[Max(2)]
        public readonly string $country = 'VN',

        #[Nullable]
        public readonly ?string $description = null,
    ) {}
}

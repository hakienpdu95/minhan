<?php

namespace Modules\Organization\Data\Requests;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\In;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class InviteUserData extends Data
{
    public function __construct(
        #[Required, Email, Max(255)]
        public readonly string $email,

        #[Required, In(['owner', 'admin', 'manager', 'member'])]
        public readonly string $role = 'member',
    ) {}
}

<?php

namespace Modules\Auth\Data;

use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

/**
 * DTO: dữ liệu đầu vào cho login form.
 * Fortify tự validate email/password; class này dùng để type-safe khi cần
 * pass vào các action hoặc service trong tương lai.
 */
class LoginData extends Data
{
    public function __construct(
        #[Required, Email, Max(255)]
        public readonly string $email,

        #[Required]
        public readonly string $password,

        public readonly bool $remember = false,
    ) {}
}

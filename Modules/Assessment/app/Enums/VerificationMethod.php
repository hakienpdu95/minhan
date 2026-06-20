<?php

namespace Modules\Assessment\Enums;

enum VerificationMethod: string
{
    case Email      = 'email';
    case PhoneOtp   = 'phone_otp';
    case VneId      = 'vne_id';
    case Passport   = 'passport';

    public function label(): string
    {
        return match ($this) {
            self::Email    => 'Email',
            self::PhoneOtp => 'Số điện thoại (OTP)',
            self::VneId    => 'VNeID',
            self::Passport => 'Hộ chiếu',
        };
    }

    public function trustLevelGranted(): int
    {
        return match ($this) {
            self::Email              => 1,
            self::PhoneOtp           => 2,
            self::VneId,
            self::Passport           => 4,
        };
    }
}

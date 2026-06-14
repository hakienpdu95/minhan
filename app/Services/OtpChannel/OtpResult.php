<?php

namespace App\Services\OtpChannel;

final class OtpResult
{
    private function __construct(
        public readonly bool    $success,
        public readonly ?string $messageId  = null,
        public readonly ?string $error      = null,
        public readonly int     $errorCode  = 0,
    ) {}

    public static function ok(?string $messageId = null): self
    {
        return new self(success: true, messageId: $messageId);
    }

    public static function fail(string $error, int $code = -1): self
    {
        return new self(success: false, error: $error, errorCode: $code);
    }
}

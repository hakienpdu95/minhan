<?php

namespace App\Services\OtpChannel\Exceptions;

use RuntimeException;

class ZbsTokenException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly bool $requiresReauth = false,
    ) {
        parent::__construct($message);
    }
}

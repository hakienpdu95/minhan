<?php

namespace App\Services\OtpChannel\Contracts;

use App\Services\OtpChannel\OtpResult;

interface OtpDriverInterface
{
    /**
     * Send a one-time verification code to the given phone number.
     *
     * @param  string $phone  Raw phone number (any Vietnamese format)
     * @param  string $code   6-digit OTP code
     */
    public function send(string $phone, string $code): OtpResult;
}

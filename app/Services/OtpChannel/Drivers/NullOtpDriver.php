<?php

namespace App\Services\OtpChannel\Drivers;

use App\Services\OtpChannel\Contracts\OtpDriverInterface;
use App\Services\OtpChannel\OtpResult;

class NullOtpDriver implements OtpDriverInterface
{
    public function send(string $phone, string $code): OtpResult
    {
        return OtpResult::ok('null-driver');
    }
}

<?php

namespace App\Services\OtpChannel\Drivers;

use App\Services\OtpChannel\Contracts\OtpDriverInterface;
use App\Services\OtpChannel\OtpResult;
use Illuminate\Support\Facades\Log;

class LogOtpDriver implements OtpDriverInterface
{
    public function send(string $phone, string $code): OtpResult
    {
        Log::channel('stack')->info('[OTP:log] Phone verification code', [
            'phone' => $phone,
            'code'  => $code,
        ]);

        return OtpResult::ok('log-driver');
    }
}

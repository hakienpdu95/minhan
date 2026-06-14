<?php

namespace App\Services\OtpChannel;

use App\Services\OtpChannel\Contracts\OtpDriverInterface;
use App\Services\OtpChannel\Drivers\LogOtpDriver;
use App\Services\OtpChannel\Drivers\NullOtpDriver;
use App\Services\OtpChannel\Drivers\ZbsZnsOtpDriver;
use InvalidArgumentException;

class OtpChannelManager
{
    /** @var array<string, OtpDriverInterface> */
    private array $resolved = [];

    /**
     * Resolve the named driver (or the configured default).
     *
     * Usage:
     *   app(OtpChannelManager::class)->driver()->send($phone, $code);
     *   app(OtpChannelManager::class)->driver('null')->send($phone, $code); // tests
     */
    public function driver(?string $name = null): OtpDriverInterface
    {
        $name ??= config('otp_channel.driver', 'log');

        return $this->resolved[$name] ??= $this->make($name);
    }

    // ── Add new drivers here — no other files need to change ──────────────────

    private function make(string $name): OtpDriverInterface
    {
        return match ($name) {
            'zbs_zns' => $this->makeZbsZns(),
            'log'     => new LogOtpDriver(),
            'null'    => new NullOtpDriver(),
            default   => throw new InvalidArgumentException("OTP driver [{$name}] is not supported."),
        };
    }

    private function makeZbsZns(): ZbsZnsOtpDriver
    {
        $cfg = config('otp_channel.drivers.zbs_zns');

        return new ZbsZnsOtpDriver(
            tokenService: app(ZbsTokenService::class),
            templateId:   $cfg['template_id'] ?? '',
            params:       $cfg['template_params'] ?? ['otp' => 'otp', 'expire_time' => 'expire_time'],
            endpoint:     $cfg['endpoints']['template'] ?? 'https://business.openapi.zalo.me/message/template',
        );
    }
}

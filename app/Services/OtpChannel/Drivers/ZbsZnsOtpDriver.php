<?php

namespace App\Services\OtpChannel\Drivers;

use App\Services\OtpChannel\Contracts\OtpDriverInterface;
use App\Services\OtpChannel\Exceptions\ZbsTokenException;
use App\Services\OtpChannel\OtpResult;
use App\Services\OtpChannel\ZbsTokenService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZbsZnsOtpDriver implements OtpDriverInterface
{
    // ZBS error codes
    private const ERR_INVALID_TOKEN   = -124;
    private const ERR_INVALID_PHONE   = -216;
    private const ERR_TEMPLATE_NOT_FOUND = -208;
    private const ERR_RATE_LIMIT      = 4;

    public function __construct(
        private readonly ZbsTokenService $tokenService,
        private readonly string          $templateId,
        private readonly array           $params,      // ['otp' => 'otp', 'expire_time' => 'expire_time']
        private readonly string          $endpoint,
    ) {}

    public function send(string $phone, string $code): OtpResult
    {
        try {
            $accessToken = $this->tokenService->getAccessToken();
        } catch (ZbsTokenException $e) {
            Log::critical('[ZBS:ZNS] Cannot get access token', ['error' => $e->getMessage()]);
            return OtpResult::fail('ZBS token unavailable: ' . $e->getMessage(), -1);
        }

        $payload = [
            'phone'        => $this->normalizePhone($phone),
            'template_id'  => $this->templateId,
            'template_data' => $this->buildTemplateData($code),
            'tracking_id'  => Str::uuid()->toString(),
        ];

        $response = Http::withHeaders(['access_token' => $accessToken])
            ->timeout(8)   // sync context — fail fast rather than block user request
            ->connectTimeout(4)
            ->post($this->endpoint, $payload);

        $body      = $response->json() ?? [];
        $errorCode = (int) ($body['error'] ?? -1);

        if ($errorCode === 0) {
            Log::info('[ZBS:ZNS] OTP sent', [
                'phone'      => $this->normalizePhone($phone),
                'msg_id'     => $body['data']['msg_id'] ?? null,
                'quota_left' => $body['data']['quota']['remainingQuota'] ?? null,
            ]);

            return OtpResult::ok($body['data']['msg_id'] ?? null);
        }

        // Expired/revoked token — invalidate cache so the next job retry fetches a fresh one
        if ($errorCode === self::ERR_INVALID_TOKEN) {
            $this->tokenService->invalidateCache();
        }

        $message = $this->translateError($errorCode, $body['message'] ?? '');

        Log::warning('[ZBS:ZNS] OTP send failed', [
            'phone'      => $this->normalizePhone($phone),
            'error_code' => $errorCode,
            'message'    => $message,
        ]);

        return OtpResult::fail($message, $errorCode);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Convert any Vietnamese phone format to the E.164 variant ZBS expects:
     *   0912345678  →  84912345678
     *   +84912345678 → 84912345678
     *   84912345678  →  84912345678 (no-op)
     */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (str_starts_with($digits, '84')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '84' . substr($digits, 1);
        }

        return $digits;
    }

    private function buildTemplateData(string $code): array
    {
        $data = [];

        foreach ($this->params as $internalKey => $zbsKey) {
            $data[$zbsKey] = match ($internalKey) {
                'otp'         => $code,
                'expire_time' => '5',
                default       => '',
            };
        }

        return $data;
    }

    private function translateError(int $code, string $raw): string
    {
        return match ($code) {
            self::ERR_INVALID_TOKEN      => 'Access token không hợp lệ hoặc đã hết hạn.',
            self::ERR_INVALID_PHONE      => 'Số điện thoại không hợp lệ hoặc không dùng Zalo.',
            self::ERR_TEMPLATE_NOT_FOUND => 'Template ZNS không tồn tại hoặc chưa được duyệt.',
            self::ERR_RATE_LIMIT         => 'Vượt quá giới hạn gửi ZNS. Thử lại sau.',
            default                      => $raw ?: "ZBS error {$code}",
        };
    }
}

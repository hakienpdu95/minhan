<?php

namespace App\Services\OtpChannel;

use App\Models\ZbsOauthToken;
use App\Services\OtpChannel\Exceptions\ZbsTokenException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZbsTokenService
{
    private string $cacheKey;

    public function __construct()
    {
        $appId           = config('otp_channel.drivers.zbs_zns.app_id', 'default');
        $this->cacheKey  = 'zbs:access_token:' . $appId;
    }

    // ── Public API ────────────────────────────────────────────────────────────

    public function getAccessToken(): string
    {
        // Fast path: warm cache
        if ($cached = Cache::get($this->cacheKey)) {
            return $cached;
        }

        $record = ZbsOauthToken::where('app_id', config('otp_channel.drivers.zbs_zns.app_id'))->first();

        if (!$record) {
            throw new ZbsTokenException(
                'ZBS token not initialised. Run: php artisan zbs:token:setup {code}',
                requiresReauth: true,
            );
        }

        if ($record->accessTokenExpiresSoon()) {
            $record = $this->refresh($record);
        }

        $ttl = max(now()->diffInSeconds($record->access_token_expires_at) - 300, 60);
        Cache::put($this->cacheKey, $record->getRawOriginal('access_token'), $ttl);

        return $record->getRawOriginal('access_token');
    }

    /** Call after receiving a -124 (invalid token) error to force re-fetch on next call. */
    public function invalidateCache(): void
    {
        Cache::forget($this->cacheKey);
    }

    /** Store initial or refreshed tokens (used by zbs:token:setup command). */
    public function storeTokens(
        string $accessToken,
        int    $accessExpiresIn,
        string $refreshToken,
        int    $refreshExpiresIn,
    ): ZbsOauthToken {
        $appId  = config('otp_channel.drivers.zbs_zns.app_id');
        $record = ZbsOauthToken::updateOrCreate(
            ['app_id' => $appId],
            [
                'access_token'             => $accessToken,
                'access_token_expires_at'  => now()->addSeconds($accessExpiresIn),
                'refresh_token'            => $refreshToken,
                'refresh_token_expires_at' => now()->addSeconds($refreshExpiresIn),
            ]
        );

        $this->invalidateCache();
        return $record;
    }

    // ── Private ───────────────────────────────────────────────────────────────

    private function refresh(ZbsOauthToken $record): ZbsOauthToken
    {
        if ($record->refreshTokenExpired()) {
            Log::critical('[ZBS] Refresh token expired — manual re-authorisation required', [
                'app_id'     => $record->app_id,
                'expired_at' => $record->refresh_token_expires_at,
            ]);

            throw new ZbsTokenException(
                'ZBS refresh token expired. Re-run: php artisan zbs:token:setup',
                requiresReauth: true,
            );
        }

        $response = Http::asForm()
            ->withHeaders(['secret_key' => config('otp_channel.drivers.zbs_zns.app_secret')])
            ->timeout(10)
            ->post(config('otp_channel.drivers.zbs_zns.endpoints.token'), [
                'app_id'        => config('otp_channel.drivers.zbs_zns.app_id'),
                'grant_type'    => 'refresh_token',
                'refresh_token' => $record->getRawOriginal('refresh_token'),
            ]);

        $data = $response->json();

        if (!isset($data['access_token'])) {
            Log::error('[ZBS] Token refresh failed', ['response' => $data]);
            throw new ZbsTokenException('ZBS token refresh failed: ' . ($data['message'] ?? 'unknown error'));
        }

        $record->update([
            'access_token'             => $data['access_token'],
            'access_token_expires_at'  => now()->addSeconds($data['expires_in'] ?? 3600),
            'refresh_token'            => $data['refresh_token'],
            // ZBS refresh tokens are valid for 90 days from last use
            'refresh_token_expires_at' => now()->addDays(90),
        ]);

        Log::info('[ZBS] Access token refreshed', ['app_id' => $record->app_id]);

        $this->invalidateCache();
        return $record->fresh();
    }
}

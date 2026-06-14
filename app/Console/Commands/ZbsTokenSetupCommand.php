<?php

namespace App\Console\Commands;

use App\Services\OtpChannel\ZbsTokenService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ZbsTokenSetupCommand extends Command
{
    protected $signature = 'zbs:token:setup
        {code : Authorization code received from the Zalo OA OAuth callback URL}
        {--code-verifier= : PKCE code verifier (required if used during the auth flow)}';

    protected $description = 'Exchange a Zalo OA authorization code for access/refresh tokens';

    public function handle(ZbsTokenService $tokenService): int
    {
        $appId     = config('otp_channel.drivers.zbs_zns.app_id');
        $appSecret = config('otp_channel.drivers.zbs_zns.app_secret');
        $endpoint  = config('otp_channel.drivers.zbs_zns.endpoints.token');

        if (!$appId || !$appSecret) {
            $this->error('ZBS_APP_ID or ZBS_APP_SECRET is not set in .env');
            return self::FAILURE;
        }

        $payload = [
            'app_id'     => $appId,
            'grant_type' => 'authorization_code',
            'code'       => $this->argument('code'),
        ];

        if ($verifier = $this->option('code-verifier')) {
            $payload['code_verifier'] = $verifier;
        }

        $this->info('Contacting Zalo OAuth endpoint...');

        $response = Http::asForm()
            ->withHeaders(['secret_key' => $appSecret])
            ->timeout(15)
            ->post($endpoint, $payload);

        $data = $response->json();

        if (empty($data['access_token'])) {
            $this->error('Token exchange failed: ' . ($data['message'] ?? json_encode($data)));
            return self::FAILURE;
        }

        // ZBS refresh tokens are valid 90 days from issuance
        $tokenService->storeTokens(
            accessToken:      $data['access_token'],
            accessExpiresIn:  (int) ($data['expires_in'] ?? 3600),
            refreshToken:     $data['refresh_token'],
            refreshExpiresIn: 90 * 86400,
        );

        $this->info('✓ ZBS tokens stored successfully.');
        $this->table(
            ['Field', 'Value'],
            [
                ['App ID',                     $appId],
                ['Access token expires in',    now()->addSeconds($data['expires_in'] ?? 3600)->diffForHumans()],
                ['Refresh token expires in',   '90 days'],
            ]
        );

        return self::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\ZbsOauthToken;
use Illuminate\Console\Command;

class ZbsTokenStatusCommand extends Command
{
    protected $signature = 'zbs:token:status';
    protected $description = 'Show current ZBS OA token status and expiry';

    public function handle(): int
    {
        $token = ZbsOauthToken::first();

        if (!$token) {
            $this->error('No ZBS token configured.');
            $this->newLine();
            $this->line('To set up:');
            $this->line('  1. Get authorization URL:');
            $authUrl = 'https://oauth.zaloapp.com/v4/oa/permission?app_id='
                . config('otp_channel.drivers.zbs_zns.app_id')
                . '&redirect_uri='
                . urlencode(config('app.url'));
            $this->line("     {$authUrl}");
            $this->line('  2. After authorising, run:');
            $this->line('     php artisan zbs:token:setup {code}');
            return self::FAILURE;
        }

        $accessOk  = $token->access_token_expires_at->isFuture();
        $refreshOk = $token->refresh_token_expires_at->isFuture();

        $this->table(
            ['Field', 'Value', 'Status'],
            [
                ['App ID',               $token->app_id,                                    ''],
                ['Driver',               config('otp_channel.driver', 'log'),               ''],
                ['Access token',         $token->access_token_expires_at->format('Y-m-d H:i'), $accessOk  ? '<fg=green>✓ Valid</>' : '<fg=red>✗ Expired</>'],
                ['Access expires',       $token->access_token_expires_at->diffForHumans(),  ''],
                ['Refresh token',        $token->refresh_token_expires_at->format('Y-m-d H:i'), $refreshOk ? '<fg=green>✓ Valid</>' : '<fg=red>✗ Expired</>'],
                ['Refresh expires',      $token->refresh_token_expires_at->diffForHumans(), ''],
                ['Updated at',           $token->updated_at->format('Y-m-d H:i:s'),         ''],
            ]
        );

        if (!$refreshOk) {
            $this->error('Refresh token expired. Re-run: php artisan zbs:token:setup {code}');
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

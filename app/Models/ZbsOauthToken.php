<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZbsOauthToken extends Model
{
    protected $table = 'zbs_oauth_tokens';

    protected $fillable = [
        'app_id',
        'access_token',
        'access_token_expires_at',
        'refresh_token',
        'refresh_token_expires_at',
    ];

    protected $hidden = ['access_token', 'refresh_token'];

    protected function casts(): array
    {
        return [
            'access_token_expires_at'  => 'datetime',
            'refresh_token_expires_at' => 'datetime',
        ];
    }

    public function accessTokenExpiresSoon(int $bufferMinutes = 5): bool
    {
        return $this->access_token_expires_at->subMinutes($bufferMinutes)->isPast();
    }

    public function refreshTokenExpired(): bool
    {
        return $this->refresh_token_expires_at->isPast();
    }
}

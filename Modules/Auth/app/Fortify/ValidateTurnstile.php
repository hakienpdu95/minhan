<?php

namespace Modules\Auth\Fortify;

use Illuminate\Http\Request;
use RyanChandler\LaravelCloudflareTurnstile\Rules\Turnstile;

/**
 * Fortify pipeline step: Cloudflare Turnstile bot protection.
 *
 * Skip khi:
 *   - APP_ENV = local | testing
 *   - TURNSTILE_ENABLED = false (default)
 *   - TURNSTILE_SITE_KEY chưa được cấu hình
 *
 * Bật production: set TURNSTILE_ENABLED=true + cả 2 keys trong .env
 */
class ValidateTurnstile
{
    public function handle(Request $request, callable $next): mixed
    {
        if ($this->shouldSkip()) {
            return $next($request);
        }

        $request->validate(
            ['cf-turnstile-response' => ['required', new Turnstile()]],
            [
                'cf-turnstile-response.required' => 'Vui lòng hoàn thành xác minh bảo mật.',
                'cf-turnstile-response.*'        => 'Xác thực bảo mật thất bại, vui lòng thử lại.',
            ]
        );

        return $next($request);
    }

    private function shouldSkip(): bool
    {
        // Luôn skip trên local / testing
        if (app()->isLocal() || app()->environment('testing')) {
            return true;
        }

        // Skip nếu chưa bật hoặc chưa cấu hình key
        if (! config('services.turnstile.enabled')) {
            return true;
        }

        if (blank(config('services.turnstile.key')) || blank(config('services.turnstile.secret'))) {
            return true;
        }

        return false;
    }
}

<?php

namespace Modules\Survey\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RyanChandler\LaravelCloudflareTurnstile\Rules\Turnstile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cloudflare Turnstile verification cho survey submit endpoint.
 *
 * Keys-as-switch: có TURNSTILE_SITE_KEY + TURNSTILE_SECRET_KEY → tự động bật.
 * Frontend phải gửi cf-turnstile-response trong request body.
 */
class ValidateSurveyTurnstile
{
    public function handle(Request $request, Closure $next): Response
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
        if (app()->isLocal() || app()->environment('testing')) {
            return true;
        }

        if (blank(config('services.turnstile.key')) || blank(config('services.turnstile.secret'))) {
            if (app()->isProduction()) {
                Log::warning('Turnstile keys chưa cấu hình — survey submit không có bot protection.');
            }
            return true;
        }

        return false;
    }
}

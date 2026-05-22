<?php

namespace Modules\Survey\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use RyanChandler\LaravelCloudflareTurnstile\Rules\Turnstile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cloudflare Turnstile verification cho survey submit endpoint.
 *
 * Skip khi: local | testing | TURNSTILE_ENABLED=false | key chưa cấu hình.
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

        if (! config('services.turnstile.enabled')) {
            return true;
        }

        if (blank(config('services.turnstile.key')) || blank(config('services.turnstile.secret'))) {
            return true;
        }

        return false;
    }
}

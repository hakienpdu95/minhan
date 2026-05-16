<?php

namespace Modules\Auth\Providers;

use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Modules\Auth\Fortify\ValidateTurnstile;
use Modules\Auth\Http\Responses\LoginResponse;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AuthServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Auth';
    protected string $nameLower = 'auth';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        // Override LoginResponse → redirect về /dashboard sau khi đăng nhập
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
    }

    public function boot(): void
    {
        // ModuleServiceProvider tự load: views, migrations, translations, config
        parent::boot();

        $this->bootFortifyViews();
        $this->bootFortifyPipeline();
    }

    // ── Fortify views → module Auth views ─────────────────────────────
    private function bootFortifyViews(): void
    {
        Fortify::loginView(fn () => view('auth::login'));
        Fortify::registerView(fn () => view('auth::register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth::passwords.email'));
        Fortify::resetPasswordView(
            fn ($request) => view('auth::passwords.reset', ['request' => $request])
        );
    }

    // ── Fortify authentication pipeline ───────────────────────────────
    // Thứ tự: throttle → turnstile → 2FA check → attempt → session
    private function bootFortifyPipeline(): void
    {
        Fortify::authenticateThrough(function () {
            return array_filter([
                config('fortify.limiters.login') ? EnsureLoginIsNotThrottled::class : null,
                ValidateTurnstile::class,
                Features::enabled(Features::twoFactorAuthentication())
                    ? RedirectIfTwoFactorAuthenticatable::class
                    : null,
                AttemptToAuthenticate::class,
                PrepareAuthenticatedSession::class,
            ]);
        });
    }
}

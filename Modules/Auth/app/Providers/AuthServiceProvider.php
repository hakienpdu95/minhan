<?php

namespace Modules\Auth\Providers;

use Laravel\Fortify\Fortify;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AuthServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Auth';
    protected string $nameLower = 'auth';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void
    {
        // ModuleServiceProvider tự load views, migrations, translations
        parent::boot();

        // Override Fortify views → dùng views của module Auth
        Fortify::loginView(fn () => view('auth::login'));
        Fortify::registerView(fn () => view('auth::register'));
        Fortify::requestPasswordResetLinkView(fn () => view('auth::passwords.email'));
        Fortify::resetPasswordView(
            fn ($request) => view('auth::passwords.reset', ['request' => $request])
        );
    }
}

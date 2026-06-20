<?php

namespace Modules\Auth\Providers;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Modules\Auth\Actions\Auth\LoginUserAction;
use Modules\Auth\Actions\Auth\RegisterUserAction;
use Modules\Auth\Actions\Auth\ResetPasswordAction;
use Modules\Auth\Actions\Auth\UpdateProfileAction;
use Modules\Auth\Fortify\ValidateTurnstile;
use Modules\Auth\Http\Responses\LoginResponse;
use Modules\Auth\Http\Responses\LogoutResponse;
use Modules\Auth\Http\Responses\RegisterResponse;
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
        parent::register(); // đăng ký EventServiceProvider + RouteServiceProvider

        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(RegisterResponseContract::class, RegisterResponse::class);
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->bootFortifyActions();
        $this->bootFortifyViews();
        $this->bootFortifyPipeline();
    }

    // ── Override Fortify actions với module Actions ────────────────────
    // Dùng app()->booted() (Application::booted) thay vì $this->booted() (ServiceProvider::booted)
    // vì ServiceProvider::booted() chỉ chạy ngay sau provider đó, không phải sau TẤT CẢ providers.
    // App\Providers\FortifyServiceProvider boot sau module này nên phải override bằng Application::booted().
    private function bootFortifyActions(): void
    {
        $this->app->booted(function () {
            Fortify::authenticateUsing(fn (Request $request) => LoginUserAction::run($request));
            Fortify::createUsersUsing(RegisterUserAction::class);
            Fortify::resetUserPasswordsUsing(ResetPasswordAction::class);
            Fortify::updateUserProfileInformationUsing(UpdateProfileAction::class);
        });
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
        Fortify::verifyEmailView(fn () => view('auth.verify-email'));

        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            return (new MailMessage)
                ->subject('Xác minh email — ' . config('app.name'))
                ->greeting('Xin chào ' . ($notifiable->name ?? 'Bạn') . '!')
                ->line('Nhấn vào nút bên dưới để xác minh địa chỉ email của bạn.')
                ->action('Xác minh email ngay', $url)
                ->line('Link có hiệu lực trong 60 phút. Nếu bạn không đăng ký tài khoản, hãy bỏ qua email này.');
        });
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

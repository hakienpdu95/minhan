<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Strict mode: prevent lazy loading, mass assignment, and missing attributes in production safety check
        Model::shouldBeStrict(!app()->isProduction());

        // Disable query log in production to save memory
        if (app()->isProduction()) {
            DB::disableQueryLog();
        }
    }
}

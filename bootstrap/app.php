<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant'        => \App\Http\Middleware\IdentifyOrganization::class,
            'assert.tenant' => \App\Http\Middleware\AssertTenant::class,
        ]);
        // InjectRequestId phải chạy đầu tiên để tất cả request đều có X-Request-Id
        $middleware->prepend(\Modules\ActivityLog\Http\Middleware\InjectRequestId::class);
        $middleware->web(\App\Http\Middleware\IdentifyOrganization::class);
        $middleware->appendToGroup('web', \Modules\ActivityLog\Http\Middleware\CaptureHttpContext::class);
        $middleware->appendToGroup('api', \Modules\ActivityLog\Http\Middleware\CaptureHttpContext::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

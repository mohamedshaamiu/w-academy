<?php

use App\Http\Middleware\ApplyStudentSessionLifetime;
use App\Http\Middleware\EnsureAgreementSigned;
use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\SetApiLocale;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(prepend: [
            ApplyStudentSessionLifetime::class,
        ]);

        $middleware->web(append: [
            SetLocale::class,
        ]);

        $middleware->api(prepend: [
            SetApiLocale::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'password.changed' => EnsurePasswordChanged::class,
            'agreement.signed' => EnsureAgreementSigned::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();

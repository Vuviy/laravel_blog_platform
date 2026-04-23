<?php

declare(strict_types=1);

use App\Http\Middleware\CheckPermissionMiddleware;
use App\Http\Middleware\LoadUser;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        using: function() {
            Route::middleware('web')
                ->group(base_path('Modules/Seo/routes/static.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            Route::middleware('web')
                ->group(base_path('routes/locale.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SetLocale::class,
            LoadUser::class,
            CheckPermissionMiddleware::class
        ]);

        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminUserMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

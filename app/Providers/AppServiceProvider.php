<?php

namespace App\Providers;

use App\Contracts\SessionManagerInterface;
use App\Services\Session\FileSessionManager;
use App\Services\Session\RedisSessionManager;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

use Illuminate\Contracts\Session\Session;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->singleton(SessionManagerInterface::class, function ($app) {
            $driver =  env('SESSION_MANAGER', 'file');

            $session = $app->make(Session::class);

            return match($driver) {
                'redis' => new RedisSessionManager($session),
                default => new FileSessionManager($session),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
    }
}

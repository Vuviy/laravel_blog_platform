<?php

namespace App\Providers;

use App\Contracts\SessionManagerInterface;
use App\Services\Session\FileSessionManager;
use App\Services\Session\RedisSessionManager;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Session\SessionManager;
use Illuminate\Support\ServiceProvider;

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
//                'redis' =>  RedisSessionManager::getInstance(),
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

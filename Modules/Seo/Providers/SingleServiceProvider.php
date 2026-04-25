<?php

namespace Modules\Seo\Providers;

use Carbon\Laravel\ServiceProvider;
use Modules\Seo\Repositories\Contracts\SeoPageRepositoryInterface;
use Modules\Seo\Services\SeoService;

class SingleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SeoService::class, function ($app) {
            return new SeoService( $app->make(SeoPageRepositoryInterface::class));
        });
    }
}

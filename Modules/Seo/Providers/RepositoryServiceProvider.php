<?php

namespace Modules\Seo\Providers;

use Carbon\Laravel\ServiceProvider;
use Illuminate\Support\Facades\Log;
use Modules\Seo\Repositories\Contracts\SeoPageRepositoryInterface;
use Modules\Seo\Repositories\SeoPageRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SeoPageRepositoryInterface::class, SeoPageRepository::class);
    }
}

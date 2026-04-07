<?php

namespace Modules\Article\Providers;

use Carbon\Laravel\ServiceProvider;
use Modules\Article\Repositories\ArticleRepository;
use Modules\Article\Repositories\Contracts\ArticleRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ArticleRepositoryInterface::class, ArticleRepository::class);
    }
}

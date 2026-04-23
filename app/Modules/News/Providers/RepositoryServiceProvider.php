<?php
declare(strict_types=1);

namespace Modules\News\Providers;

use Carbon\Laravel\ServiceProvider;
use Modules\News\Repositories\Contracts\NewsRepositoryInterface;
use Modules\News\Repositories\NewsRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NewsRepositoryInterface::class, NewsRepository::class);
    }
}

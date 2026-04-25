<?php

namespace Modules\Tags\Providers;

use Carbon\Laravel\ServiceProvider;

use Modules\Tags\Repositories\Contracts\TagRepositoryInterface;
use Modules\Tags\Repositories\TagRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
    }
}

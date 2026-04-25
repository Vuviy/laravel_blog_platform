<?php

namespace Modules\Comments\Providers;

use Carbon\Laravel\ServiceProvider;
use Modules\Comments\Repositories\CommentRepository;
use Modules\Comments\Repositories\Contracts\CommentRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CommentRepositoryInterface::class, CommentRepository::class);
    }
}

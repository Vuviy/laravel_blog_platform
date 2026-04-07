<?php

namespace Modules\Users\Providers;

use Carbon\Laravel\ServiceProvider;
use Modules\Users\Repositories\Contracts\PermissionRepositoryInterface;
use Modules\Users\Repositories\Contracts\RoleRepositoryInterface;
use Modules\Users\Repositories\Contracts\UserRepositoryInterface;
use Modules\Users\Repositories\PermissionRepository;
use Modules\Users\Repositories\RoleRepository;
use Modules\Users\Repositories\UserRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
    }
}

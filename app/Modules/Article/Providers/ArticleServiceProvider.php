<?php

namespace Modules\Article\Providers;

use Modules\Article\Repositories\ArticleRepository;
use Modules\Article\Repositories\Contracts\ArticleRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class ArticleServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Article';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'article';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
        RepositoryServiceProvider::class,
    ];

    /**
     * Define module schedules.
     *
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }

//    public function register(): void
//    {
//        $this->app->bind(
//            ArticleRepositoryInterface::class,
//            ArticleRepository::class
//        );
//    }
}

<?php

namespace Modules\Article\Providers;

use App\Contracts\FilterInterface;
use Modules\Article\Filter\ArticleFilter;
use Modules\Article\Repositories\ArticleTaggableRepository;
use Modules\Tags\Services\TaggableRegistry;
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


    public function boot(): void
    {
        parent::boot();

        $this->app->make(TaggableRegistry::class)
            ->register(
                $this->app->make(ArticleTaggableRepository::class)
            );
    }
    /**
     * Define module schedules.
     *
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }

    public function register(): void
    {

        parent::register();

        $this->app->bind(
            FilterInterface::class,
            ArticleFilter::class
        );
    }
}

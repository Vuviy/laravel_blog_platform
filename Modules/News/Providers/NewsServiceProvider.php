<?php

namespace Modules\News\Providers;

use Modules\News\Repositories\NewsTaggableRepository;
use Modules\Tags\Services\TaggableRegistry;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class NewsServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'News';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'news';

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
                $this->app->make(NewsTaggableRepository::class)
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
}

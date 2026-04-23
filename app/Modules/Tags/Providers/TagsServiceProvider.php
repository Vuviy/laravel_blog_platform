<?php

namespace Modules\Tags\Providers;

use Modules\Tags\Services\TaggableRegistry;
use Nwidart\Modules\Support\ModuleServiceProvider;

class TagsServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Tags';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'tags';

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


    public function register(): void
    {
        parent::register();

        $this->app->singleton(TaggableRegistry::class, function () {
            return new TaggableRegistry();
        });
    }

    public function boot(): void
    {
        parent::boot();
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

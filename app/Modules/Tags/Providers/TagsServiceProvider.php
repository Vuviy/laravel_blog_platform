<?php

namespace Modules\Tags\Providers;

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

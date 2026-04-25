<?php

namespace Modules\Seo\Providers;

use Modules\Seo\Services\SeoService;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class SeoServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Seo';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'seo';

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
        SingleServiceProvider::class,
    ];


//    public function register(): void
//    {
//        $this->app->singleton(SeoService::class, function () {
//            return new SeoService();
//        });
//
////        $this->app->register(RouteServiceProvider::class);
//    }
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

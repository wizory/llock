<?php

namespace Wizory\Llock;

use App;
use Illuminate\Support\ServiceProvider;

class LlockServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot() {
        # define published resources and groups
        $this->publishes([
            __DIR__.'/config/llock.php' => config_path('llock.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/database/migrations/' => database_path('migrations')
        ], 'database');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register() {
        # merge our config with app so users can override just some elements in published version
        $this->mergeConfigFrom(
            __DIR__.'/config/llock.php', 'llock'
        );

        # register commands
        $this->commands([
            'Wizory\Llock\Console\Commands\LlockStatus',
            'Wizory\Llock\Console\Commands\LlockSet',
            'Wizory\Llock\Console\Commands\LlockFree',
            'Wizory\Llock\Console\Commands\LlockInstall',
        ]);

        # NOTE not currently used
        # register our kernel (defines any scheduled tasks)
//        $this->app->singleton('wizory.llock.console.kernel', function($app) {
//            $dispatcher = $app->make(\Illuminate\Contracts\Events\Dispatcher::class);
//            return new \Wizory\Llock\Console\Kernel($app, $dispatcher);
//        });
//        $this->app->make('wizory.llock.console.kernel');

        # NOTE no controller necessary yet
//        $this->app->make('Wizory\Llock\Http\Controllers\LlockController');
    }
}

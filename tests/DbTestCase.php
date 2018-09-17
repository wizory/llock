<?php


use Illuminate\Filesystem\ClassFinder;
use Illuminate\Filesystem\Filesystem;

abstract class DbTestCase extends Illuminate\Foundation\Testing\TestCase {
    /**
     * Boots the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication() {
        $app = require __DIR__ . '/../vendor/laravel/laravel/bootstrap/app.php';

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        $app->register('Wizory\Llock\LlockServiceProvider');

        return $app;
    }

    /**
     * Setup DB before each test.
     *
     * @return void
     */
    public function setUp() {
        parent::setUp();

        $db = 'testing.db';

        file_put_contents($db, '');

        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite.database', 'testing.db');
        $this->app['config']->set('debug', 'true');

        $this->migrate();
    }

    /**
     * run package database migrations
     *
     * @return void
     */
    public function migrate()
    {
        $fileSystem = new Filesystem;
        $classFinder = new ClassFinder;

        foreach($fileSystem->files(__DIR__ . "/../src/database/migrations") as $file)
        {
            $fileSystem->requireOnce($file);
            $migrationClass = $classFinder->findClass($file);

            (new $migrationClass)->up();
        }
    }
}
<?php

namespace Wizory\Llock\Console\Commands;

use Illuminate\Console\Command;
use Log;

use Wizory\Llock\Models\Lock;

class LlockSet extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'llock:set
                            {name : Name of the lock to set}
                            {--wait : Waits for a lock if one is already set}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attempts to set a lock named {name}';

    /**
     * Create a new command instance.
     *
     */
    public function __construct() {
        parent::__construct();
    }

    public function setLock($name) {
        $this->info('Attempting to set lock ' . $name);

        return Lock::set($name);
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        try {
            $name = $this->argument('name');

            $lock = $this->setLock($name);

            $retry = config('llock.wait-retry');
            $timeout = config('llock.timeout');

            # TODO DRY up this block
            if (empty($lock)) {
                $this->info('Lock already exists');

                if ($this->option('wait')) {

                    $start = time();

                    # TODO output how long we've waited out of the total wait?
                    while (time() - $start < $timeout) {
                        $this->info("Waiting ${retry} seconds...");

                        sleep($retry);

                        $lock = $this->setLock($name);

                        if (! empty($lock)) {
                            $this->info('Lock created successfully');

                            exit(Lock::SUCCESS);
                        }
                    }

                    $this->info("Failed to obtain lock...timeout of ${timeout} seconds reached.");
                    exit(Lock::FAILED);

                } else {
                    $this->info('Failed to obtain lock...use --wait to wait for it.');
                    exit(Lock::FAILED);
                }
            } else {
                $this->info('Lock created successfully');

                exit(Lock::SUCCESS);
            }

            if (empty($lock) && $this->option('wait')) {
                $this->info('Waiting...');  # TODO add retry and timeout info
            } else {

            }
        } catch (\Exception $e) {
            $this->debug($e->getTraceAsString());

            exit (Lock::ERROR);
        }
    }
}

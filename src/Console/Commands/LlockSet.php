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
        Lock::Log($this, 'Attempting to set lock ' . $name);

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
                Lock::Log($this, 'Lock already exists');

                if ($this->option('wait')) {

                    $start = time();

                    # TODO output how long we've waited out of the total wait?
                    while (time() - $start < $timeout) {
                        Lock::Log($this,"Waiting ${retry} seconds...");

                        sleep($retry);

                        $lock = $this->setLock($name);

                        if (! empty($lock)) {
                            Lock::Log($this, 'Lock created successfully');

                            exit(Lock::SUCCESS);
                        }
                    }

                    Lock::Log($this, "Failed to obtain lock...timeout of ${timeout} seconds reached.");
                    exit(Lock::FAILED);

                } else {
                    Lock::Log($this, 'Failed to obtain lock...use --wait to wait for it.');
                    exit(Lock::FAILED);
                }
            } else {
                Lock::Log($this, 'Lock created successfully');

                exit(Lock::SUCCESS);
            }
        // only log a note since this is an expected event (but it is still an error as far as getting the lock)
        } catch (\Illuminate\Database\QueryException $e) {
            Log::debug('Lock already in progress...');

            exit (Lock::ERROR);

        } catch (\Exception $e) {
            Log::error($e->getMessage() . " exception obtaining lock ${name}: " . $e->getTraceAsString());

            exit (Lock::ERROR);
        }
    }
}

<?php

namespace Wizory\Llock\Console\Commands;

use Illuminate\Console\Command;
use Log;
use Wizory\Llock\Models\Lock;

class LlockFree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'llock:free {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attempts to free a lock named {name}';

    /**
     * Create a new command instance.
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $name = $this->argument('name');

        Lock::log($this, 'Attempting to free lock ' . $name);

        try {
            Lock::free($this->argument('name'));
        } catch (\Exception $e) {
            // TODO catch database exceptions, etc. here?
        }
    }
}

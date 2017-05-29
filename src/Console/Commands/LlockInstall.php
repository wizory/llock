<?php

namespace Wizory\Llock\Console\Commands;

use Illuminate\Console\Command;
use Log;

class LlockInstall extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'llock:install';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '(re)install wizory/llock and dependencies';
    /**
     * Create a new command instance.
     *
     */
    public function __construct() {
        parent::__construct();
    }
    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $this->info('[ Llock Installation ]');

        # publish assets
        $this->call('vendor:publish', array(
            '--provider' => 'Wizory\Llock\LlockServiceProvider',
        ));

        # run migration(s)
        $this->call('migrate');
    }
}

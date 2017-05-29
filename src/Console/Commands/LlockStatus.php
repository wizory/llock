<?php

namespace Wizory\Llock\Console\Commands;

use Illuminate\Console\Command;
use Log;
use Wizory\Llock\Models\Lock;

class LlockStatus extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'llock:status';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'show status of any existing locks';
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
        $this->info('[ Llock Status ]');

        $headers = ['Name', 'Created'];

        $locks = Lock::all(['name', 'created_at'])->toArray();

        $this->table($headers, $locks);
    }
}

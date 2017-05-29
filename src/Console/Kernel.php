<?php

namespace XFactor\Blueprint\Console;

use App\Console\Kernel as AppKernel;
use Illuminate\Console\Scheduling\Schedule;

class Kernel extends AppKernel {
    /**
     * Define the package's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {
        parent::schedule($schedule);

//        $schedule->command('some:command')->everyMinute();
    }
}
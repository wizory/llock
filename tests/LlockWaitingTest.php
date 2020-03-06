<?php

use Wizory\Llock\Models\Lock;

require_once __DIR__ . '/DbTestCase.php';

class LlockWaitingTest extends DbTestCase {

    public function testLockWait() {
        $lockName = 'testLockWait';

        $this->app['config']->set('llock.wait-retry', '1');
        $this->app['config']->set('llock.timeout', '10');

        $pid = pcntl_fork();

        if ($pid) { // parent

            sleep(2);  // give child time to set lock

            $this->assertDatabaseHas('llocks', ['name' => $lockName]);

            fwrite(STDERR, print_r("- setting parent lock\n", TRUE));
            $resultSet = Artisan::call('llock:set', array(
                'name' => $lockName,
                '--wait' => null,
            ));

            $this->assertDatabaseHas('llocks', ['name' => $lockName]);
            $this->assertEquals(Lock::SUCCESS, $resultSet);

            fwrite(STDERR, print_r("- freeing parent lock\n", TRUE));
            $resultFree = Artisan::call('llock:free', array(
                'name' => $lockName,
            ));

            $this->assertDatabaseMissing('llocks', ['name' => $lockName]);
            $this->assertEquals(Lock::SUCCESS, $resultFree);

            pcntl_wait($status); // protect against Zombie Children

        } else { // child

            fwrite(STDERR, print_r("- setting child lock\n", TRUE));

            $resultSet = Artisan::call('llock:set', array(
                'name' => $lockName,
            ));

            $this->assertEquals(Lock::SUCCESS, $resultSet);

            sleep(5);

            fwrite(STDERR, print_r("- freeing child lock\n", TRUE));
            $resultFree = Artisan::call('llock:free', array(
                'name' => $lockName,
            ));

            $this->assertEquals(Lock::SUCCESS, $resultFree);

            die();  // kill off child process so phpUnit doesn't get confused
        }
    }

    public function testLockTimeout() {
        $lockName = 'testLockTimeout';

        $this->app['config']->set('llock.wait-retry', '1');
        $this->app['config']->set('llock.timeout', '3');

        $pid = pcntl_fork();

        if ($pid) { // parent

            fwrite(STDERR, print_r("- setting parent lock\n", TRUE));
            $resultSet = Artisan::call('llock:set', array(
                'name' => $lockName,
            ));

            $this->assertDatabaseHas('llocks', ['name' => $lockName]);
            $this->assertEquals(Lock::SUCCESS, $resultSet);

            sleep(7);

            fwrite(STDERR, print_r("- freeing parent lock\n", TRUE));
            $resultFree = Artisan::call('llock:free', array(
                'name' => $lockName,
            ));

            $this->assertDatabaseMissing('llocks', ['name' => $lockName]);
            $this->assertEquals(Lock::SUCCESS, $resultFree);

            pcntl_wait($status); // protect against Zombie Children

        } else { // child

            sleep(2);  // give parent time to set lock

            $this->assertDatabaseHas('llocks', ['name' => $lockName]);

            fwrite(STDERR, print_r("- setting child lock\n", TRUE));

            $resultSet = Artisan::call('llock:set', array(
                'name' => $lockName,
                '--wait' => null,
            ));

            $this->assertEquals(Lock::FAILED, $resultSet);

            die();  // kill off child process so phpUnit doesn't get confused
        }
    }

}

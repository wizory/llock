<?php

use Wizory\Llock\Models\Lock;

require_once __DIR__ . '/DbTestCase.php';

class LlockRaceTest extends DbTestCase {

    public function testLockStuckIterations() {
        $lockName = 'testLockStuckIterations';

        $sync_wait = 1;  // wait time in seconds to ensure lock attempts are synchronous
        $lock_sleep = 2;  // time in seconds to keep lock
        $iterations = 100;

        for ($i = 0; $i < $iterations; $i++) {

            $sleep_until = time() + $sync_wait;

            $pid = pcntl_fork();

            if ($pid) { // parent

                $time = time();

                fwrite(STDERR, print_r("- parent sleeping until ${sleep_until} " .
                    "(current time: ${time} \n", TRUE));

                time_sleep_until($sleep_until);

                fwrite(STDERR, print_r("- setting parent lock\n", TRUE));
                $resultSet = Artisan::call('llock:set', array(
                    'name' => $lockName,
                ));

                if ($resultSet == Lock::SUCCESS) {

                    fwrite(STDERR, print_r("- obtained parent lock\n", TRUE));

                    $this->seeInDatabase('llocks', ['name' => $lockName]);

                    sleep($lock_sleep);

                    fwrite(STDERR, print_r("- freeing parent lock\n", TRUE));

                    $resultFree = Artisan::call('llock:free', array(
                        'name' => $lockName,
                    ));

                    if ($resultFree == Lock::SUCCESS) {
                        fwrite(STDERR, print_r("- freed parent lock\n", TRUE));
                        $this->dontSeeInDatabase('llocks', ['name' => $lockName]);
                    } elseif ($resultFree == LOCK::FAILED) {
                        fwrite(STDERR, print_r("- failed to free parent lock\n", TRUE));
                        $this->seeInDatabase('llocks', ['name' => $lockName]);
                    } elseif ($resultFree == LOCK::ERROR) {
                        fwrite(STDERR, print_r("- error freeing parent lock\n", TRUE));
                        $this->seeInDatabase('llocks', ['name' => $lockName]);
                    } else {
                        fwrite(STDERR, print_r("- unknown result freeing parent lock\n", TRUE));
                        $this->seeInDatabase('llocks', ['name' => $lockName]);
                    }

                } elseif ($resultSet == LOCK::FAILED) {
                    fwrite(STDERR, print_r("- failed to set parent lock\n", TRUE));
                } elseif ($resultSet == LOCK::ERROR) {
                    fwrite(STDERR, print_r("- error setting parent lock\n", TRUE));
                } else {
                    fwrite(STDERR, print_r("- unknown result setting parent lock\n", TRUE));
                    $this->dontSeeInDatabase('llocks', ['name' => $lockName]);
                }

            } else { // child

                $time = time();

                fwrite(STDERR, print_r("- child sleeping until ${sleep_until} " .
                    "(current time: ${time} \n", TRUE));

                time_sleep_until($sleep_until);

                fwrite(STDERR, print_r("- setting child lock\n", TRUE));
                $resultSet = Artisan::call('llock:set', array(
                    'name' => $lockName,
                ));

                if ($resultSet == Lock::SUCCESS) {

                    fwrite(STDERR, print_r("- obtained child lock\n", TRUE));

                    $this->seeInDatabase('llocks', ['name' => $lockName]);

                    sleep($lock_sleep);

                    fwrite(STDERR, print_r("- freeing child lock\n", TRUE));

                    $resultFree = Artisan::call('llock:free', array(
                        'name' => $lockName,
                    ));

                    if ($resultFree == Lock::SUCCESS) {
                        fwrite(STDERR, print_r("- freed child lock\n", TRUE));
                        $this->dontSeeInDatabase('llocks', ['name' => $lockName]);
                    } elseif ($resultFree == LOCK::FAILED) {
                        fwrite(STDERR, print_r("- failed to free child lock\n", TRUE));
                        $this->seeInDatabase('llocks', ['name' => $lockName]);
                    } elseif ($resultFree == LOCK::ERROR) {
                        fwrite(STDERR, print_r("- error freeing child lock\n", TRUE));
                        $this->seeInDatabase('llocks', ['name' => $lockName]);
                    } else {
                        fwrite(STDERR, print_r("- unknown result freeing child lock\n", TRUE));
                        $this->seeInDatabase('llocks', ['name' => $lockName]);
                    }

                } elseif ($resultSet == LOCK::FAILED) {
                    fwrite(STDERR, print_r("- failed to set child lock\n", TRUE));
                } elseif ($resultSet == LOCK::ERROR) {
                    fwrite(STDERR, print_r("- error setting child lock\n", TRUE));
                } else {
                    fwrite(STDERR, print_r("- unknown result setting child lock\n", TRUE));
                    $this->dontSeeInDatabase('llocks', ['name' => $lockName]);
                }

                die();  // kill off child process so phpUnit doesn't get confused
            }

            pcntl_wait($status); // protect against Zombie Children

            $this->dontSeeInDatabase('llocks', ['name' => $lockName]);

        }
    }

    public function testLockStuckClients() {
        $lockName = 'testLockStuckClients';

        $sync_wait = 7;  // seconds for all children to wait before attempting to obtain a lock
        $lock_sleep = 2;  // time in seconds to keep lock
        $clients = 300;

        for ($c = 0; $c < $clients; $c++) {

            switch ($pid = pcntl_fork()) {
                case -1:
                    fwrite(STDERR, print_r("- fork failed client: ${c}\n", TRUE));

                    // die('Fork failed');
                    break;
                case 0:
                    fwrite(STDERR, print_r("* forked child client: ${c}\n", TRUE));

                    $sleep_until = time() + $sync_wait;
                    $time = time();

                    fwrite(STDERR, print_r("- sleeping until ${sleep_until} " .
                        "(current time: ${time} client: ${c}\n", TRUE));

                    time_sleep_until($sleep_until);

                    fwrite(STDERR, print_r("- setting child lock\n", TRUE));
                    $resultSet = Artisan::call('llock:set', array(
                        'name' => $lockName,
                    ));

                    if ($resultSet == Lock::SUCCESS) {

                        fwrite(STDERR, print_r("- obtained child lock\n", TRUE));

                        $this->seeInDatabase('llocks', ['name' => $lockName]);

                        sleep($lock_sleep);

                        fwrite(STDERR, print_r("- freeing child lock\n", TRUE));

                        $resultFree = Artisan::call('llock:free', array(
                            'name' => $lockName,
                        ));

                        if ($resultFree == Lock::SUCCESS) {
                            fwrite(STDERR, print_r("- freed child lock\n", TRUE));
                            $this->dontSeeInDatabase('llocks', ['name' => $lockName]);
                        } elseif ($resultFree == LOCK::FAILED) {
                            fwrite(STDERR, print_r("- failed to free child lock\n", TRUE));
                            $this->seeInDatabase('llocks', ['name' => $lockName]);
                        } elseif ($resultFree == LOCK::ERROR) {
                            fwrite(STDERR, print_r("- error freeing child lock\n", TRUE));
                            $this->seeInDatabase('llocks', ['name' => $lockName]);
                        } else {
                            fwrite(STDERR, print_r("- unknown result freeing child lock\n", TRUE));
                            $this->seeInDatabase('llocks', ['name' => $lockName]);
                        }

                    } elseif ($resultSet == LOCK::FAILED) {
                        fwrite(STDERR, print_r("- failed to set child lock\n", TRUE));
                    } elseif ($resultSet == LOCK::ERROR) {
                        fwrite(STDERR, print_r("- error setting child lock\n", TRUE));
                    } else {
                        fwrite(STDERR, print_r("- unknown result setting child lock\n", TRUE));
                        $this->dontSeeInDatabase('llocks', ['name' => $lockName]);
                    }

                    die();  // kill off child process so phpUnit doesn't get confused

                    break;

                default:
                    fwrite(STDERR, print_r("- parent not waiting client: ${c}\n", TRUE));
                    break;
            }
        }

        pcntl_waitpid($pid, $status);  // wait for all children to die (sad!)

        sleep(10);  // wait for all sleeping children to awake! (making of a Black Sabbath song here...)

        $this->assertTrue(True);
    }

}

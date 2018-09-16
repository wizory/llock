<?php

use Wizory\Llock\Models\Lock;

require_once __DIR__ . '/DbTestCase.php';

class LlockTest extends DbTestCase {

    public function testLockSet() {
        $lockName = 'testLockSet';

        $result = Artisan::call('llock:set', array(
            'name' => $lockName,
        ));

        $this->seeInDatabase('llocks', ['name' => $lockName]);
        $this->assertEquals(Lock::SUCCESS, $result);
    }

    public function testDupeLockSet() {
        $lockName = 'testDupeLockSet';

        $resultInitial =  Artisan::call('llock:set', array(
            'name' => $lockName,
        ));
        $resultDupe = Artisan::call('llock:set', array(
            'name' => $lockName,
        ));

        $this->seeInDatabase('llocks', ['name' => $lockName]);
        $this->assertEquals(Lock::SUCCESS, $resultInitial);
        $this->assertEquals(Lock::FAILED, $resultDupe);
    }

    public function testLockFree() {
        $lockName = 'testltestLockFreeock';

        Artisan::call('llock:set', array(
            'name' => $lockName,
        ));

        $result = Artisan::call('llock:free', array(
            'name' => $lockName,
        ));

        $this->dontSeeInDatabase('llocks', ['name' => $lockName]);
        $this->assertEquals(0, $result);
    }

    public function testLockDupeFree() {
        $lockName = 'testLockDupeFree';

        Artisan::call('llock:set', array(
            'name' => $lockName,
        ));

        $resultInitial = Artisan::call('llock:free', array(
            'name' => $lockName,
        ));
        $resultDupe = Artisan::call('llock:free', array(
            'name' => $lockName,
        ));

        $this->dontSeeInDatabase('llocks', ['name' => $lockName]);
        $this->assertEquals(0, $resultDupe);
    }

}

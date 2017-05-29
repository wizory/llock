<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class LlockTest extends \PHPUnit\Framework\TestCase {

    # TODO figure out how to use Laravel database class pointed to a local sqllite or similar db
    public function testTrue() {
        $this->assertTrue(true);
    }
}

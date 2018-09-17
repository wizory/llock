<?php

use Wizory\Llock\Models\Lock;

require_once __DIR__ . '/DbTestCase.php';

class LlockShellTest extends DbTestCase {

// TODO write shell scripts to test various return code scenarios (use PHPUnit to assert result of those scripts)

// This will ensure we don't break runtime usage via `php artisan` commands...need to ensure it's always bubbling
// the return value up as an exit code for the process (other shell scripts use the result to determine behavior)

}

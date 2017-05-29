<?php

return [
    # amount of time to wait between retries for obtaining a lock (when --wait is used)
    'wait-retry' => 10,  # seconds

    # timeout when waiting to obtain a lock (returns a failure after this time expires)
    'timeout' => 600,  # seconds (5 minutes)
];

<?php

namespace Wizory\Llock\Models;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Log;

/**
 * Class Lock
 * @package Wizory\Llock\Lock
 *
 * At this point we drop the Llama facade/charade (British pronunciation) and just call it a Lock (This is safe since
 * we're in our own namespace...ah serenity now).
 */
class Lock extends Model {
    const SUCCESS = 0;
    const FAILED = 1;
    const ERROR = 2;

    protected $guarded = [];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'llocks';

    # NOTE 'name' is logically the primary key, but the ability to use a non-incrementing PK wasn't added until
    #      Laravel 5.2 :-(

    /**
     * Attempts to set a lock.
     *
     * @param $name
     * @return lock if successful, null if it already exists
     */
    public static function set($name) {
        return \DB::transaction(function() use ($name) {
            $lock = Lock::lockForUpdate()->where('name', $name)->get();

            # if no lock exists, create it and return it
            if ($lock->isEmpty()) {
                $lock = Lock::create(['name' => $name]);

                return $lock;
            }

            return null;
        });
    }

    /**
     * Attempts to free an existing lock.
     *
     * @param $name
     */
    public static function free($name) {
        return \DB::transaction(function() use ($name) {
            $lock = Lock::lockForUpdate()->where('name', $name)->get();

            # if we have an existing lock with that name, remove it
            if (! $lock->isEmpty()) {
                $lock->first()->delete();
            }

            # TODO might want to return the lock that was freed and null otherwise so the caller can tell what happened

            # otherwise the result is the same...lock is gone (or never existed). ;)
        });
    }

    /**
     * Logs messages to console / laravel based on config
     *
     * @param Command $command
     * @param $message
     */
    public static function log(Command $command, $message) {
        if ($command->option('verbose')) $command->info($message);

        Log::debug($message);
    }
}

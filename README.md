Laravel Lock Plugin (llock)
======

*[insert Llama picture here]*

This plugin adds a set of artisan commands to set and free shared named locks.  The current primary use-case is with 
[Laravel forge](http://forge.laravel.com) when using multiple load-balanced servers.

The locking provided via this plugin enables the following:

#### Serial web instance deployment
Using the `--wait` mode allows each web instance to deploy sequentially based on who has the lock.  The lock is 
freed as each deploy completes so the next can obtain it.  This keeps at least one instance up and serving traffic at
 all times.

#### Same scheduled task on all instances (without redundant runs)
The scheduled task run can be configured on all web instances, but only the one who obtains the lock will execute the
 task.  This allows a more fault-tolerant setup since any web instance(s) can be lost and the scheduled task(s) will 
 continue to run (but it won't run multiple times when more than one instance is "up").
 

## Setup / Installation
This plugin is intended to work with an existing Laravel 5.1 LTS (Long-term Support) site/app.  Steps to add the plugin 
follow.

### add github repository to `composer.json`
This allows composer to retrieve the package dependencies directly from github.  This step will become unnecessary once 
the plugin is officially "released".

Update your `composer.json` file with a new `repositories` element (typically placed just before the `require` block):
```json
"repositories": [
  {
    "type": "vcs",
    "url": "git@github.com:wizory/llock.git"
  }
],
```

### add plugin dependency to `composer.json`

Now add the plugin dependency to the existing `require` block:
```json
"require": {
  ...,
  "wizory/llock": "dev-master"
},
```
The version specified for `wizory/llock` in the `require` section references the `master` branch of the repository.

Upon release, the example will be updated to point to a release version.

### update composer
Now run `composer update` to fetch llock and its dependencies.

### register llock service provider
Add the following line to `config/app.php` in the providers array:
```
Wizory\Llock\LlockServiceProvider::class,
```

### run installer

This command will install an example config and migration(s) necessary for the plugin to function:
```
php artisan llock:install

```

The newly added files should be committed to your project.

**NOTE:** This command will run migrations.  If you have any outstanding migrations pending they should be resolved 
prior to running install.

## Usage
At this point you should be able to run `php artisan llock:status` and get some output.  You can pass `-h` to any 
command to get usage details also.

### `php artisan llock:status`
Shows the status of any current locks.

### `php artisan llock:set <name>`
Sets a lock named `<name>`.  Passing `--wait` will wait a configurable amount of time to obtain the named 
lock if it already exists with the same name.  The plugin will retry periodically to obtain the lock (also 
configurable).

The return code of this command is 0 (true) if a lock was obtained and 1 (false) otherwise.  A return code of 2 (also
 false) indicates an error (e.g. database connection).

### `php artisan llock:free <name>`
Frees the lock named `<name>`.

The return code of this command is 0 whether the lock was freed or not, and 2 in case of error.

## Examples

### Ensuring sequential deployment across multiple site instances
Add something like this to your deployment script/code:

```bash
LOCKNAME=mysite

php artisan lock:set --wait ${LOCKNAME}  # command will not return until a lock is obtained or the timeout is reached

# deploy steps here....

php artisan lock:free ${LOCKNAME}

```

This example mostly assumes a "happy path".  You may want to detect the return code and fail (before deploy) on 
timeout or other error.

### Ensuring scheduled tasks only run once across a set of multiple site instances
Use something like this for your CRON/scheduler entry:

```bash
LOCKNAME=mysite php artisan lock:set ${LOCKNAME} && php artisan schedule:run; php artisan lock:free ${LOCKNAME}

# or the mostly equivalent

if php artisan lock:set mysite; then php artisan run; fi; php artisan lock:free mysite

```

Again, this example doesn't explicitly handle the variety of error cases that can arise.  The (handy) one-liner can 
get messy so you might want to call an intermediate script from your scheduler.

## Updating

To update to new versions of llock, use the following steps:

```
composer update wizory/llock
php artisan llock:install

```

Commit any new/changed files afterwards.


## Security

There is an assumption of trust here as any "client" could free a lock created by any other.  This is not a useful 
implementation for locking in an untrusted environment.
 

# DRAFT CONTENT BELOW

The documentation, etc. below is all Work In Progress (WIP) and will be revised, organized, and moved to the above section as it takes shape.
 
---

### Running Tests
1. once you've setup the above, login to your vagrant machine, cd to the site directory and run:
```
vendor/bin/phpunit
```

Use the `--colors` flag if you want color output.

You should see lots of tests run and pass. :)

### Roadmap

As Laravel udates the LTS designation to newer versions, this plugin will be updated to work with them if any 
breaking changes are introduced.


## Contributing

**`TODO:`** Provide further instructions for dev setup and contributing.

**NOTE:** For local development on the llock package itself, use the following (changing the relative path to the 
llock package as-needed):
```json
"repositories": [
    {
        "type": "path",
        "url": "../llock"
    }
],
"require": {
  ...,
  "wizory/llock": "*@dev"
},
```

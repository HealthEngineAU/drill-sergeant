<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache connection that gets used for
    | storing metrics data.
    |
    | Supports any laravel cache store that also supports locking, these are
    | currently redis, memcached and dynamodb.
    |
    */

    'cache_store' => env('DRILL_SERGEANT_CACHE_STORE', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Cache Prefix
    |--------------------------------------------------------------------------
    |
    | This option changes the prefix added to all cache items managed by this
    | package.
    |
    */

    'cache_prefix' => env('DRILL_SERGEANT_CACHE_PREFIX', 'drillsergeant'),

    /*
    |--------------------------------------------------------------------------
    | Cache Lock Timeout
    |--------------------------------------------------------------------------
    |
    | This option sets the time in seconds to wait for an atomic lock to update
    | the statistics stored in the cache.
    |
    */

    'cache_lock_timeout' => env('DRILL_SERGEANT_CACHE_LOCK_TIMEOUT', 1),
];

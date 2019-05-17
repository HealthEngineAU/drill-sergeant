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
    | Supports any laravel cache store configured in your config/cache.php.
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
];

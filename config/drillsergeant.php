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

];

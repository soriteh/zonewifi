<?php
use Illuminate\Support\Str;
return [
    'default' => env('CACHE_STORE', 'redis'),
    'stores' => [
        'array' => ['driver' => 'array', 'serialize' => false],
        'database' => ['driver' => 'database', 'table' => 'cache', 'lock_table' => 'cache_locks'],
        'redis' => ['driver' => 'redis', 'connection' => 'cache', 'lock_connection' => 'default'],
    ],
    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'zonewifi'), '_').'_cache_'),
];

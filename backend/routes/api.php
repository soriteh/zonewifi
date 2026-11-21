<?php

use Illuminate\Support\Facades\Route;

Route::get('/status', fn () => response()->json([
    'application' => 'ZoneWifi',
    'status' => 'ok',
    'version' => '0.1.0',
]));

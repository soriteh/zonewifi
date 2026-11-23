<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RouterController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::resource('users', UserController::class)->except(['show']);
    Route::post('/routers/{router}/test', [RouterController::class, 'testConnection'])->name('routers.test');
    Route::get('/routers/{router}/hotspot', [RouterController::class, 'hotspot'])->name('routers.hotspot');
    Route::post('/routers/{router}/hotspot/disconnect', [RouterController::class, 'disconnect'])->name('routers.disconnect');
    Route::post('/routers/{router}/hotspot/users', [RouterController::class, 'createHotspotUser'])->name('routers.hotspot.users.store');
    Route::delete('/routers/{router}/hotspot/users', [RouterController::class, 'deleteHotspotUser'])->name('routers.hotspot.users.destroy');
    Route::resource('routers', RouterController::class)->except(['show']);
});

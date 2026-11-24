<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('zonewifi:about', function () {
    $this->info('ZoneWifi - socle technique Laravel / Docker');
})->purpose('Affiche les informations du projet ZoneWifi');

Schedule::command('zonewifi:monitor-routers')->everyFiveMinutes()->withoutOverlapping();

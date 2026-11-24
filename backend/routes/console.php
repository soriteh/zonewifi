<?php

use Illuminate\Support\Facades\Artisan;

Artisan::command('zonewifi:about', function () {
    $this->info('ZoneWifi - socle technique Laravel / Docker');
})->purpose('Affiche les informations du projet ZoneWifi');\n\nSchedule::command('zonewifi:monitor-routers')->everyFiveMinutes()->withoutOverlapping();

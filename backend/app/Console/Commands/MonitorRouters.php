<?php

namespace App\\Console\\Commands;

use App\\Models\\Router;
use App\\Services\\Mikrotik\\RouterOsApiClient;
use Illuminate\\Console\\Command;
use Throwable;

class MonitorRouters extends Command
{
    protected $signature = 'zonewifi:monitor-routers';
    protected $description = 'Vérifie la disponibilité des routeurs MikroTik enregistrés';

    public function handle(RouterOsApiClient $client): int
    {
        $online = 0;
        $offline = 0;

        Router::query()->orderBy('id')->chunkById(50, function ($routers) use ($client, &$online, &$offline): void {
            foreach ($routers as $router) {
                try {
                    $info = $client->inspect(
                        $router->host,
                        (int) $router->api_port,
                        $router->username,
                        $router->password_encrypted,
                        4
                    );

                    $router->update([
                        'status' => 'online',
                        'routeros_version' => $info['version'],
                        'last_seen_at' => now(),
                    ]);
                    $online++;
                } catch (Throwable $exception) {
                    $router->update(['status' => 'offline']);
                    $offline++;
                    report($exception);
                }
            }
        });

        $this->info("Supervision terminée : {$online} en ligne, {$offline} hors ligne.");
        return self::SUCCESS;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'pageTitle' => 'Tableau de bord',
            'modules' => [
                ['label' => 'Sites Wi-Fi', 'description' => 'Gestion des zones et points d’accès', 'icon' => 'wifi'],
                ['label' => 'Routeurs', 'description' => 'Inventaire et supervision MikroTik', 'icon' => 'router'],
                ['label' => 'Forfaits', 'description' => 'Tarifs, durées et profils Hotspot', 'icon' => 'layers'],
                ['label' => 'Abonnés', 'description' => 'Clients et historique des accès', 'icon' => 'users'],
                ['label' => 'Tickets', 'description' => 'Création et suivi des tickets', 'icon' => 'ticket'],
                ['label' => 'Ventes', 'description' => 'Encaissements et reversements', 'icon' => 'wallet'],
            ],
        ]);
    }
}

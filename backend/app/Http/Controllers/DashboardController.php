<?php

namespace App\\Http\\Controllers;

use App\\Models\\Customer;
use App\\Models\\Router;
use App\\Models\\Sale;
use App\\Models\\User;
use App\\Models\\WifiPlan;
use App\\Models\\WifiTicket;
use Illuminate\\Support\\Carbon;
use Illuminate\\View\\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $companyId = auth()->user()->company_id;
        $companyScope = static fn ($query) => $query->where('company_id', $companyId);

        $salesQuery = Sale::query()->where('company_id', $companyId);
        $todaySales = (clone $salesQuery)->whereDate('sold_at', today());
        $monthSales = (clone $salesQuery)->whereBetween('sold_at', [now()->startOfMonth(), now()->endOfMonth()]);

        $dailySales = collect(range(6, 0))->map(function (int $daysAgo) use ($companyId): array {
            $date = now()->subDays($daysAgo)->startOfDay();
            return [
                'label' => $date->locale('fr')->translatedFormat('D d'),
                'amount' => (int) Sale::query()->where('company_id', $companyId)
                    ->whereDate('sold_at', $date->toDateString())->sum('amount'),
            ];
        });

        return view('dashboard', [
            'pageTitle' => 'Tableau de bord',
            'metrics' => [
                'routers' => Router::query()->where('company_id', $companyId)->count(),
                'onlineRouters' => Router::query()->where('company_id', $companyId)->where('status', 'online')->count(),
                'plans' => WifiPlan::query()->where('company_id', $companyId)->where('is_active', true)->count(),
                'customers' => Customer::query()->where('company_id', $companyId)->count(),
                'tickets' => WifiTicket::query()->where('company_id', $companyId)->count(),
                'salesCount' => (clone $salesQuery)->count(),
                'todayRevenue' => (int) $todaySales->sum('amount'),
                'monthRevenue' => (int) $monthSales->sum('amount'),
            ],
            'dailySales' => $dailySales,
            'recentSales' => (clone $salesQuery)->with(['customer', 'seller', 'ticket.plan'])
                ->latest('sold_at')->limit(8)->get(),
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

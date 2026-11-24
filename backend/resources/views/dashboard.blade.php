<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }} · ZoneWifi</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <a class="brand" href="{{ route('dashboard') }}"><span class="brand-mark">Z</span><span>Zone<span class="accent">Wifi</span><small>GESTION HOTSPOT</small></span></a>
        <div class="nav-caption">ESPACE DE GESTION</div>
        <a class="nav-link active" href="{{ route('dashboard') }}">▦ <span>Tableau de bord</span></a>
        <a class="nav-link" href="#modules">⌁ <span>Modules</span></a>
        @if(in_array(auth()->user()->role, ["admin", "manager"], true))<a class="nav-link" href="{{ route('routers.index') }}">⌁ <span>Routeurs MikroTik</span></a>@endif
        <a class="nav-link" href="{{ route('tickets.index') }}">▤ <span>Tickets Wi-Fi</span></a>
        @if(auth()->user()->role === "admin")<a class="nav-link" href="{{ route('users.index') }}">♙ <span>Utilisateurs</span></a>@endif
        <div class="sidebar-bottom"><span class="status-dot"></span> Socle technique initial <small>v0.1.0</small></div>
    </aside>
    <main class="main">
        <header class="topbar"><div><span class="eyebrow">ZONEWIFI / APERÇU</span><h1>{{ $pageTitle }}</h1></div><div class="top-actions"><span class="top-tag">ENVIRONNEMENT LOCAL</span><span class="user-greeting">{{ auth()->user()->name }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-button" type="submit">Déconnexion</button></form></div></header>
        <section class="welcome"><div><span class="eyebrow light">BIENVENUE SUR ZONEWIFI</span><h2>Connectez et gérez<br>vos zones Wi-Fi.</h2><p>Une plateforme centralisée pour administrer vos hotspots, routeurs, clients et ventes.</p></div><div class="welcome-symbol">⌁</div></section>
        <section class="metrics-grid">
            <article class="metric-card"><span class="metric-label">Routeurs enregistrés</span><strong>{{ number_format($metrics['routers'], 0, ',', ' ') }}</strong><small><span class="metric-live">{{ number_format($metrics['onlineRouters'], 0, ',', ' ') }} en ligne</span></small></article>
            <article class="metric-card"><span class="metric-label">Forfaits actifs</span><strong>{{ number_format($metrics['plans'], 0, ',', ' ') }}</strong><small>Offres commercialisables</small></article>
            <article class="metric-card"><span class="metric-label">Clients</span><strong>{{ number_format($metrics['customers'], 0, ',', ' ') }}</strong><small>Base clients de l’entreprise</small></article>
            <article class="metric-card"><span class="metric-label">Tickets générés</span><strong>{{ number_format($metrics['tickets'], 0, ',', ' ') }}</strong><small>Tickets enregistrés</small></article>
            <article class="metric-card"><span class="metric-label">Ventes enregistrées</span><strong>{{ number_format($metrics['salesCount'], 0, ',', ' ') }}</strong><small>Transactions enregistrées</small></article>
            <article class="metric-card metric-highlight"><span class="metric-label">Recettes du jour</span><strong>{{ number_format($metrics['todayRevenue'], 0, ',', ' ') }} <small>GNF</small></strong><small>Montant des ventes aujourd’hui</small></article>
            <article class="metric-card metric-highlight"><span class="metric-label">Recettes du mois</span><strong>{{ number_format($metrics['monthRevenue'], 0, ',', ' ') }} <small>GNF</small></strong><small>Depuis le début du mois</small></article>
            <article class="metric-card"><span class="metric-label">Disponibilité routeurs</span><strong>{{ $metrics['routers'] > 0 ? number_format(($metrics['onlineRouters'] / $metrics['routers']) * 100, 0, ',', ' ') : 0 }}%</strong><small>Routeurs signalés en ligne</small></article>
        </section>
        <section class="dashboard-panels">
            <article class="panel-card"><div class="panel-heading"><div><span class="eyebrow">ACTIVITÉ COMMERCIALE</span><h2>Ventes des 7 derniers jours</h2></div><span class="muted">Montants en GNF</span></div>
                <div class="sales-bars">
                    @php($maxDaily = max(1, $dailySales->max('amount')))
                    @foreach($dailySales as $day)
                        <div class="sales-bar-item"><strong>{{ number_format($day['amount'], 0, ',', ' ') }}</strong><div class="sales-bar-track"><span style="height: {{ max(4, ($day['amount'] / $maxDaily) * 100) }}%"></span></div><small>{{ $day['label'] }}</small></div>
                    @endforeach
                </div>
            </article>
            <article class="panel-card"><div class="panel-heading"><div><span class="eyebrow">DERNIÈRES OPÉRATIONS</span><h2>Ventes récentes</h2></div></div>
                @if($recentSales->isEmpty())
                    <p class="empty-sales">Aucune vente enregistrée pour le moment.</p>
                @else
                    <div class="recent-sales-list">
                    @foreach($recentSales as $sale)
                        <div class="recent-sale"><div><strong>{{ $sale->customer?->name ?? 'Client comptoir' }}</strong><small>{{ $sale->sold_at?->format('d/m/Y H:i') ?? 'Date non renseignée' }} · {{ $sale->seller?->name ?? 'Vendeur non renseigné' }}</small></div><b>{{ number_format($sale->amount, 0, ',', ' ') }} {{ $sale->currency ?? 'GNF' }}</b></div>
                    @endforeach
                    </div>
                @endif
            </article>
        </section>
        <section class="section-head" id="modules"><div><span class="eyebrow">VOTRE ACTIVITÉ</span><h2>Modules de gestion</h2></div><span class="muted">Modules en cours de construction</span></section>
        <section class="module-grid">
            @foreach ($modules as $module)
                <article class="module-card"><div class="module-icon">{{ strtoupper(substr($module['label'], 0, 1)) }}</div><h3>{{ $module['label'] }}</h3><p>{{ $module['description'] }}</p>@if($module['label'] === 'Routeurs' && in_array(auth()->user()->role, ["admin", "manager"], true))<a class="module-action" href="{{ route('routers.index') }}">Ouvrir le module →</a>@elseif($module['label'] === 'Tickets')<a class="module-action" href="{{ route('tickets.index') }}">Ouvrir le module →</a>@else<span class="module-state">À développer</span>@endif</article>
            @endforeach
        </section>
        <footer>ZoneWifi · Soritech Innov Business · Socle technique initial</footer>
    </main>
</div>
</body>
</html>

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
        <div class="sidebar-bottom"><span class="status-dot"></span> Socle technique initial <small>v0.1.0</small></div>
    </aside>
    <main class="main">
        <header class="topbar"><div><span class="eyebrow">ZONEWIFI / APERÇU</span><h1>{{ $pageTitle }}</h1></div><div class="top-tag">ENVIRONNEMENT LOCAL</div></header>
        <section class="welcome"><div><span class="eyebrow light">BIENVENUE SUR ZONEWIFI</span><h2>Connectez et gérez<br>vos zones Wi-Fi.</h2><p>Une plateforme centralisée pour administrer vos hotspots, routeurs, clients et ventes.</p></div><div class="welcome-symbol">⌁</div></section>
        <section class="section-head" id="modules"><div><span class="eyebrow">VOTRE ACTIVITÉ</span><h2>Modules de gestion</h2></div><span class="muted">Modules en cours de construction</span></section>
        <section class="module-grid">
            @foreach ($modules as $module)
                <article class="module-card"><div class="module-icon">{{ strtoupper(substr($module['label'], 0, 1)) }}</div><h3>{{ $module['label'] }}</h3><p>{{ $module['description'] }}</p><span class="module-state">À développer</span></article>
            @endforeach
        </section>
        <footer>ZoneWifi · Soritech Innov Business · Socle technique initial</footer>
    </main>
</div>
</body>
</html>

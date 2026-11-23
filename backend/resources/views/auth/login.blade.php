<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion · ZoneWifi</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="auth-page">
    <main class="auth-shell">
        <section class="auth-brand-panel">
            <a class="brand auth-brand" href="{{ route('login') }}">
                <span class="brand-mark">Z</span>
                <span>Zone<span class="accent">Wifi</span><small>GESTION HOTSPOT</small></span>
            </a>
            <div class="auth-message">
                <span class="eyebrow light">SORITECH INNOV BUSINESS</span>
                <h1>Pilotez votre réseau Wi-Fi en toute simplicité.</h1>
                <p>Centralisez vos routeurs, forfaits, tickets, ventes et opérations depuis un seul espace.</p>
            </div>
            <span class="auth-version">ZONEWIFI · PLATEFORME DE GESTION</span>
        </section>
        <section class="auth-form-panel">
            <form class="auth-form" method="POST" action="{{ route('login.store') }}">
                @csrf
                <span class="eyebrow">ESPACE ADMINISTRATEUR</span>
                <h2>Connexion</h2>
                <p class="auth-subtitle">Saisissez vos identifiants pour accéder à votre espace.</p>

                @if ($errors->any())
                    <div class="auth-error" role="alert">{{ $errors->first() }}</div>
                @endif

                <label for="email">Adresse e-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus placeholder="admin@exemple.com">

                <label for="password">Mot de passe</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="Votre mot de passe">

                <button class="auth-submit" type="submit">Se connecter <span aria-hidden="true">→</span></button>
                <p class="auth-help">Accès réservé aux utilisateurs autorisés de votre établissement.</p>
            </form>
        </section>
    </main>
</body>
</html>

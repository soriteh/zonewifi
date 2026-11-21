# Architecture ZoneWifi

## Socle initial
- Laravel 12 monolithique pour l'API et les vues.
- PostgreSQL comme source de vérité.
- Redis pour le cache, les sessions et les files de traitement.
- Nginx comme serveur web FastCGI vers PHP-FPM.
- Docker Compose pour le développement local.

## Isolation des entreprises
Les tables métier portent un company_id. Toutes les lectures et écritures métier devront appliquer un scope d'entreprise côté serveur. Le company_id ne doit jamais être accepté aveuglément depuis une requête cliente.

## MikroTik
Les connexions RouterOS seront exécutées exclusivement côté serveur. Les mots de passe des routeurs sont stockés via le cast encrypted du modèle Router. Les ports API ne doivent pas être exposés sur Internet. Prévoir TLS/VPN ou un réseau privé entre le serveur et les routeurs avant toute production.

## Étapes suivantes
1. Authentification, rôles et permissions.
2. Middleware d'isolation multi-entreprises et tests d'accès.
3. Client RouterOS avec timeouts, journalisation et gestion des erreurs.
4. Gestion des forfaits, tickets, ventes et reversements.
5. Supervision des sessions Hotspot.
6. Sauvegardes PostgreSQL, audit et tests de restauration.

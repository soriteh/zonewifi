# Développement local

Le port HTTP de l'application est lié à 127.0.0.1:8080. PostgreSQL est publié sur 127.0.0.1:5433 et Redis sur 127.0.0.1:6379 pour les outils locaux.

Les identifiants de base inclus dans docker-compose.yml sont uniquement des valeurs de développement local. Les remplacer avant toute utilisation partagée.

Les fichiers backend sont montés dans le conteneur pour permettre l'édition depuis Windows. Après modification de composer.json, reconstruire l'image avec docker compose build app.

La clé d'application est conservée dans le fichier .env local. Après le premier démarrage, exécuter : docker compose exec app php artisan key:generate.

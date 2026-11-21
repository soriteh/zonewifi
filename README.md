# ZoneWifi

Plateforme de gestion de zones Wi-Fi et de Hotspot MikroTik.

## Stack
- Laravel 12 / PHP 8.3
- PostgreSQL 16
- Redis 7
- Nginx
- Docker Compose

## Prérequis
- Docker Desktop (Windows) avec le moteur Linux activé
- Git

## Démarrage local

1. Cloner le dépôt : `git clone https://github.com/soriteh/zonewifi.git`
2. Entrer dans le dossier : `cd zonewifi`
3. Créer la configuration : `cp .env.example .env`
4. Construire et démarrer : `docker compose up -d --build`
5. Générer la clé Laravel : `docker compose exec app php artisan key:generate`
6. Exécuter les migrations : `docker compose exec app php artisan migrate --seed`
7. Ouvrir http://localhost:8080

## Services
- Application : http://localhost:8080
- PostgreSQL : localhost:5433 (développement local)
- Redis : localhost:6379

## Commandes utiles
- État : `docker compose ps`
- Journaux : `docker compose logs -f app`
- Migrations : `docker compose exec app php artisan migrate`
- Arrêt : `docker compose down`
- Suppression destructive des données : `docker compose down -v`

## Sécurité
- Ne jamais versionner le fichier `.env`.
- Ne pas exposer les identifiants MikroTik dans le frontend.
- Injecter les secrets par l’environnement d’exécution.
- Cette configuration est destinée au développement local, pas à une exposition directe sur Internet.

## État du projet
Socle technique initial. Les modules métiers et l’intégration réelle à RouterOS seront développés et testés par étapes.

#!/usr/bin/env bash
# MenuPro — Script de déploiement (à exécuter sur le serveur après git pull)
# Usage: ./deploy.sh  ou  bash deploy.sh

set -e

echo "=== MenuPro — Déploiement ==="

# Répertoire du projet (adapter si besoin)
cd "$(dirname "$0")"

echo ">> Composer install..."
composer install --no-dev --optimize-autoloader --no-interaction

echo ">> Migrations..."
php artisan migrate --force

echo ">> Build des assets..."
npm ci
npm run build

echo ">> Caches Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ">> Redémarrage des workers (queue)..."
php artisan queue:restart 2>/dev/null || true

echo "=== Déploiement terminé. ==="

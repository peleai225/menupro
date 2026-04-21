#!/usr/bin/env bash
# MenuPro — Script de déploiement
# Usage: bash ~/deploy.sh [branche]
#   ex: bash deploy.sh                  → branche actuelle
#   ex: bash deploy.sh main             → merge depuis main
#   ex: bash deploy.sh claude/qr-code-dimensions-J89Ib

set -e

BRANCH="${1:-}"   # branche passée en argument (optionnel)

echo "=== MenuPro — Déploiement ==="
echo ">> Répertoire : $(pwd)"

# Se placer à la racine du projet
cd "$(dirname "$0")"

# ─── 1. Récupérer les nouvelles modifications ───────────────────────────────
echo ""
echo ">> Git fetch + pull..."
git fetch origin

if [ -n "$BRANCH" ]; then
    echo "   Passage sur la branche : $BRANCH"
    git checkout "$BRANCH"
fi

CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
echo "   Branche active : $CURRENT_BRANCH"
git pull origin "$CURRENT_BRANCH"
echo "   Dernier commit : $(git log --oneline -1)"

# ─── 2. Dépendances PHP ─────────────────────────────────────────────────────
echo ""
echo ">> Composer install..."
composer install --no-dev --optimize-autoloader --no-interaction

# ─── 3. Migrations ──────────────────────────────────────────────────────────
echo ""
echo ">> Migrations..."
php artisan migrate --force

# ─── 4. Assets Vite ─────────────────────────────────────────────────────────
echo ""
echo ">> Build des assets..."
npm ci --silent
npm run build

# ─── 5. Caches Laravel ──────────────────────────────────────────────────────
echo ""
echo ">> Nettoyage et recachage..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# ─── 6. Workers queue ───────────────────────────────────────────────────────
echo ""
echo ">> Redémarrage des workers..."
php artisan queue:restart 2>/dev/null || true

# ─── 7. PHP-FPM (vider l'opcache) ───────────────────────────────────────────
# Décommentez la ligne qui correspond à votre version de PHP :
# sudo systemctl reload php8.2-fpm
# sudo systemctl reload php8.3-fpm

echo ""
echo "=== Déploiement terminé ==="
echo "   Branche : $CURRENT_BRANCH"
echo "   Commit  : $(git log --oneline -1)"

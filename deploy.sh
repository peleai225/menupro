#!/bin/bash
# MenuPro — Script de déploiement (cPanel / shared hosting)
# Usage: bash ~/deploy.sh
echo " Déploiement MenuPro..."

# 1. Pull le dernier code
cd ~/MenuPro
git stash -q 2>/dev/null
git pull origin main
git stash pop -q 2>/dev/null || true

# 1b. Installer/mettre à jour les dépendances PHP
composer install --no-dev --no-interaction --optimize-autoloader 2>/dev/null || true

# 1c. Régénérer le classmap (trouve les nouveaux composants Livewire, etc.)
composer dump-autoload --optimize --no-dev 2>/dev/null || true

# 2. Copier les assets publics vers public_html
echo " Copie des assets publics..."
cp -r public/build/* ~/public_html/build/ 2>/dev/null
cp -r public/images/* ~/public_html/images/ 2>/dev/null
cp -r public/sounds/* ~/public_html/sounds/ 2>/dev/null
mkdir -p ~/public_html/sounds 2>/dev/null
cp -r public/sounds/* ~/public_html/sounds/ 2>/dev/null
cp public/icon-*.png ~/public_html/ 2>/dev/null
cp public/manifest.json ~/public_html/ 2>/dev/null
cp public/manifest-admin.json ~/public_html/ && echo " manifest-admin.json copié" || echo " ERREUR copie manifest-admin.json"
cp public/sw.js ~/public_html/ 2>/dev/null
cp public/firebase-messaging-sw.js ~/public_html/ 2>/dev/null
cp public/offline.html ~/public_html/ 2>/dev/null
cp public/favicon.svg ~/public_html/ 2>/dev/null
cp public/robots.txt ~/public_html/ 2>/dev/null

# Copier aussi vers menupro.ci/ si le dossier existe (domaine sans www)
if [ -d ~/menupro.ci ]; then
    cp public/manifest-admin.json ~/menupro.ci/ 2>/dev/null && echo " manifest-admin.json copié → menupro.ci/"
    cp public/manifest.json ~/menupro.ci/ 2>/dev/null
    cp public/sw.js ~/menupro.ci/ 2>/dev/null
    cp public/firebase-messaging-sw.js ~/menupro.ci/ 2>/dev/null
fi

# 2b. Symlink storage (images téléchargées via Storage::url)
echo " Lien symbolique storage..."
ln -sfn ~/MenuPro/storage/app/public ~/public_html/storage 2>/dev/null || true

# 2c. Corriger APP_URL et FILESYSTEM_DISK si valeurs de dev présentes
if grep -q "APP_URL=http://MenuPro.test" ~/MenuPro/.env 2>/dev/null; then
    sed -i 's|APP_URL=http://MenuPro.test|APP_URL=https://www.menupro.ci|g' ~/MenuPro/.env
    echo " APP_URL corrigé → https://www.menupro.ci"
fi
if grep -q "FILESYSTEM_DISK=local" ~/MenuPro/.env 2>/dev/null; then
    sed -i 's|FILESYSTEM_DISK=local|FILESYSTEM_DISK=public|g' ~/MenuPro/.env
    echo " FILESYSTEM_DISK corrigé → public"
fi

# 3. Migrations
echo " Migrations..."
php artisan migrate --force 2>/dev/null || true

# 4. Vider les caches Laravel
echo " Nettoyage cache..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan route:cache

echo ""
echo " DÉPLOIEMENT TERMINÉ !"
echo " $(date)"

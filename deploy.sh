#!/bin/bash
# MenuPro — Script de déploiement (cPanel / shared hosting)
# Usage: bash ~/deploy.sh
echo " Déploiement MenuPro..."

# 1. Pull le dernier code
cd ~/MenuPro
git pull origin main

# 2. Copier les assets publics vers public_html
echo " Copie des assets publics..."
cp -r public/build/* ~/public_html/build/ 2>/dev/null
cp -r public/images/* ~/public_html/images/ 2>/dev/null
cp -r public/sounds/* ~/public_html/sounds/ 2>/dev/null
mkdir -p ~/public_html/sounds 2>/dev/null
cp -r public/sounds/* ~/public_html/sounds/ 2>/dev/null
cp public/icon-*.png ~/public_html/ 2>/dev/null
cp public/manifest.json ~/public_html/ 2>/dev/null
cp public/sw.js ~/public_html/ 2>/dev/null
cp public/offline.html ~/public_html/ 2>/dev/null
cp public/favicon.svg ~/public_html/ 2>/dev/null
cp public/robots.txt ~/public_html/ 2>/dev/null

# 3. Migrations
echo " Migrations..."
php artisan migrate --force 2>/dev/null || true

# 4. Vider les caches Laravel
echo " Nettoyage cache..."
php artisan view:clear
php artisan cache:clear
php artisan route:cache
php artisan config:clear

echo ""
echo " DÉPLOIEMENT TERMINÉ !"
echo " $(date)"

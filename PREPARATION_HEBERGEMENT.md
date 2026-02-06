# 📦 Préparation pour l'hébergement - MenuPro

## ✅ État actuel

- [x] Assets compilés (`npm run build` exécuté)
- [x] Dossier `public/build/` créé avec les fichiers CSS/JS
- [x] Caches Laravel vidés
- [x] Structure des dossiers vérifiée

## 📋 Checklist avant upload

### Fichiers à INCLURE dans le ZIP :
- ✅ Tout le projet SAUF les exclusions ci-dessous
- ✅ `public/build/` (assets compilés) - **IMPORTANT !**
- ✅ `vendor/` (dépendances Composer) - **OBLIGATOIRE !** (Composer n'est pas disponible sur le serveur)
- ✅ Tous les fichiers PHP, migrations, configs, etc.

### Fichiers à EXCLURE du ZIP :
- ❌ `node_modules/` (trop lourd, pas nécessaire)
- ❌ `.env` (créer un nouveau sur le serveur avec les bonnes valeurs)
- ❌ `storage/logs/*.log` (logs locaux)
- ❌ `storage/framework/cache/*` (cache local)
- ❌ `storage/framework/sessions/*` (sessions locales)
- ❌ `storage/framework/views/*` (vues compilées locales)
- ❌ `.git/` (optionnel)

## 🚀 Étapes d'upload

1. **Créer le ZIP** du projet (en excluant les fichiers listés ci-dessus)
2. **Uploader** le ZIP sur le serveur via cPanel File Manager
3. **Décompresser** dans le dossier `MenuuPro` (ou le nom de ton dossier)
4. **Créer le `.env`** sur le serveur avec les bonnes valeurs

## ⚙️ Configuration `.env` sur le serveur

Crée un fichier `.env` dans `MenuuPro/` avec :

```env
APP_NAME=MenuPro
APP_ENV=production
APP_KEY=                    # Générer avec: php artisan key:generate
APP_DEBUG=false
APP_URL=https://menupro.ci

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=menupro1_menupro
DB_USERNAME=menupro1_peleai
DB_PASSWORD=ton_mot_de_passe_serveur

SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=no-reply@menupro.ci
MAIL_PASSWORD=Wondercoder2022@
MAIL_FROM_ADDRESS="no-reply@menupro.ci"
MAIL_FROM_NAME="${APP_NAME}"
```

## 🔧 Commandes à exécuter sur le serveur (Terminal cPanel)

**Note :** Composer n'est pas disponible sur le serveur, donc `vendor/` doit être inclus dans le ZIP.

```bash
cd ~/MenuuPro

# 1. Vérifier que vendor/ existe (doit être dans le ZIP)
ls -la vendor/autoload.php
# Si cette commande échoue, vendor/ n'a pas été uploadé correctement !

# 2. Générer APP_KEY
php artisan key:generate

# 3. Créer les dossiers manquants
mkdir -p storage/framework/sessions
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p storage/app/public

# 4. Créer le lien symbolique pour les images (IMPORTANT pour afficher les images !)
php artisan storage:link

# 5. Permissions
chmod -R 775 storage bootstrap/cache

# 6. Migrations
php artisan migrate --force

# 7. Vider les caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 8. Optimiser pour la production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## ✅ Vérifications finales

- [ ] `vendor/` existe sur le serveur (vérifier avec `ls -la vendor/autoload.php`)
- [ ] `public/build/` existe sur le serveur
- [ ] `.env` créé avec les bonnes valeurs
- [ ] `APP_KEY` générée
- [ ] Dossiers `storage/framework/` créés
- [ ] **Lien symbolique `public/storage` créé** (vérifier avec `ls -la public/storage`)
- [ ] Permissions 775 sur `storage/` et `bootstrap/cache/`
- [ ] Migrations exécutées
- [ ] Caches optimisés
- [ ] Site accessible sur https://menupro.ci

## 🐛 En cas d'erreur 500

1. Vérifier les logs : `MenuuPro/storage/logs/laravel.log`
2. Vérifier les permissions : `chmod -R 775 storage bootstrap/cache`
3. Vérifier le `.env` : identifiants DB corrects
4. Vider les caches : `php artisan config:clear && php artisan config:cache`

## 🖼️ Images ne s'affichent pas ?

Si les images des plats ne s'affichent pas :

1. **Créer le lien symbolique** (obligatoire !) :
   ```bash
   cd ~/MenuuPro
   php artisan storage:link
   ```

2. **Vérifier que le lien existe** :
   ```bash
   ls -la public/storage
   # Doit afficher quelque chose comme : lrwxrwxrwx ... storage -> ../storage/app/public
   ```

3. **Vérifier les permissions** :
   ```bash
   chmod -R 775 storage/app/public
   ```

4. **Vérifier que les images sont bien uploadées** :
   ```bash
   ls -la storage/app/public/restaurants/
   ```

---

**Date de préparation :** $(Get-Date -Format "yyyy-MM-dd HH:mm:ss")

# Déployer MenuPro sur cPanel

Ce guide décrit comment héberger le projet Laravel MenuPro sur un hébergeur utilisant **cPanel**.

---

## 1. Prérequis côté hébergement

- **PHP ≥ 8.2** (vérifier dans cPanel → Sélecteur de version PHP / MultiPHP INI Editor)
- **Composer** disponible en SSH ou via « Terminal » cPanel
- **MySQL/MariaDB** (créer une base et un utilisateur dans cPanel → MySQL® Databases)
- **Extension PHP** : au minimum `openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `curl`

---

## 2. Préparer le projet en local

### 2.1 Installer les dépendances pour la production (sans dev)

```bash
composer install --no-dev --optimize-autoloader
```

### 2.2 Compiler les assets (JS/CSS)

Si vous utilisez Vite :

```bash
npm ci
npm run build
```

### 2.3 Générer la clé d’application

```bash
php artisan key:generate
```

Copiez la valeur de `APP_KEY` dans votre fichier `.env` de production.

### 2.4 Fichiers à ne pas envoyer sur le serveur

Ne uploadez **pas** :

- `.env` (vous le créerez sur le serveur)
- `node_modules/`
- `.git/`
- Fichiers de tests / IDE
- `storage/logs/*` et `storage/framework/cache/data/*` (optionnel, peuvent être vides)

---

## 3. Structure des dossiers sur cPanel

Sur cPanel, le site est en général servi depuis `public_html`. Pour Laravel, deux approches sont possibles.

### Option A : Document root = dossier Laravel (recommandé si possible)

Si votre hébergeur permet de définir le **document root** (par domaine ou sous-domaine) vers un sous-dossier :

- Uploadez tout le projet Laravel dans un dossier, par ex. `menupro/` (à la racine de votre compte, pas dans `public_html`).
- Définissez le **document root** du domaine vers : `menupro/public`

Ainsi, seul `public` est exposé au web, ce qui est sécurisé.

### Option B : Tout dans public_html (très courant)

Si vous ne pouvez pas changer le document root :

1. **À la racine du compte** (au même niveau que `public_html`), créez un dossier, ex. `menupro_app`.
2. Uploadez **tout** le projet Laravel dans `menupro_app/` (y compris `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `vendor/`, etc.).
3. Dans `public_html`, supprimez le contenu par défaut et mettez **uniquement** le contenu du dossier `public` de Laravel :
   - `index.php`
   - `favicon.ico` (si vous en avez un)
   - dossiers `build/` (assets compilés) et éventuellement `css/`, `js/` si vous les utilisez

4. **Modifier `public_html/index.php`** pour que Laravel trouve l’application dans `menupro_app` :

   En haut du fichier, remplacez les lignes qui chargent le projet par quelque chose comme :

   ```php
   <?php

   use Illuminate\Foundation\Application;
   use Illuminate\Http\Request;

   define('LARAVEL_START', microtime(true));

   $appPath = dirname(__DIR__) . '/menupro_app';  // chemin vers la racine Laravel

   if (file_exists($maintenance = $appPath . '/storage/framework/maintenance.php')) {
       require $maintenance;
   }

   require $appPath . '/vendor/autoload.php';

   /** @var Application $app */
   $app = require_once $appPath . '/bootstrap/app.php';

   $app->handleRequest(Request::capture());
   ```

   Adaptez `menupro_app` au nom du dossier que vous avez créé.

---

## 4. Fichier .env sur le serveur

Dans le dossier **racine Laravel** (par ex. `menupro_app/` ou `menupro/`), créez un fichier `.env` à partir de `.env.example` :

- Copiez `.env.example` en `.env`
- Renseignez au minimum :
  - `APP_KEY=base64:...` (générée en local ou avec `php artisan key:generate` sur le serveur)
  - `APP_ENV=production`
  - `APP_DEBUG=false`
  - `APP_URL=https://votre-domaine.com`
  - `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_HOST` (souvent `localhost` sur cPanel)
  - Options mail, Lygos, GeniusPay, etc. selon votre configuration

---

## 5. Permissions

À exécuter dans le dossier racine Laravel (en SSH ou Terminal cPanel) :

```bash
chmod -R 755 storage bootstrap/cache
chown -R VOTRE_UTILISATEUR:VOTRE_GROUPE storage bootstrap/cache
```

Sous cPanel, l’utilisateur est en général votre identifiant de compte. Si vous n’avez pas SSH, utilisez le Gestionnaire de fichiers cPanel (clic droit → Permissions) : `storage` et `bootstrap/cache` en 755, et écriture pour le propriétaire.

---

## 6. Commandes Laravel sur le serveur

Toujours depuis la **racine du projet Laravel** (pas depuis `public_html`) :

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
```

`--force` est nécessaire en production pour les migrations.

---

## 7. Base de données

- Dans cPanel → **MySQL® Databases** : créez une base (ex. `votrelog_menupro`) et un utilisateur, puis associez l’utilisateur à la base avec tous les privilèges.
- Indiquez le nom de la base, l’utilisateur et le mot de passe dans le `.env` (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`). `DB_HOST` est souvent `localhost`.

---

## 8. Vérifications

- Ouvrir `https://votre-domaine.com` : la page d’accueil doit s’afficher.
- Vérifier les logs en cas d’erreur : `storage/logs/laravel.log` (et éventuellement `payments-*.log`).
- Si erreur 500 : vérifier les permissions `storage` / `bootstrap/cache`, la présence de `APP_KEY`, et que le chemin vers l’application dans `public_html/index.php` est correct (option B).

---

## 9. Résumé des étapes

| Étape | Action |
|-------|--------|
| 1 | PHP 8.2+ et extensions activées dans cPanel |
| 2 | Créer la base MySQL et l’utilisateur |
| 3 | `composer install --no-dev --optimize-autoloader` et `npm run build` en local |
| 4 | Uploader le projet (hors `public` dans `public_html` si option B) |
| 5 | Créer `.env` et configurer APP_KEY, DB_*, APP_URL |
| 6 | Permissions sur `storage` et `bootstrap/cache` |
| 7 | Adapter `public_html/index.php` si option B |
| 8 | `php artisan config:cache`, `route:cache`, `view:cache`, `migrate --force` |

Une fois ces points respectés, MenuPro est prêt à être utilisé sur votre cPanel.

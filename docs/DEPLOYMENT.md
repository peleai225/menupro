# MenuPro — Guide de déploiement

Ce document décrit comment préparer et déployer MenuPro en production (VPS, serveur dédié ou hébergement PHP).

---

## 1. Prérequis serveur

- **PHP** 8.2 ou 8.3 avec extensions : `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `gd` ou `imagick`
- **Composer** 2.x
- **Node.js** 18+ et **npm** (pour le build des assets)
- **MySQL** 8.0+ (ou MariaDB 10.3+)
- **Serveur web** : Nginx ou Apache avec mod_rewrite

Vérification PHP :

```bash
php -v
php -m  # liste les modules
```

---

## 2. Déploiement initial

### 2.1 Cloner le projet et installer les dépendances

```bash
cd /var/www  # ou votre répertoire
git clone <url-du-repo> menupro
cd menupro
```

### 2.2 Dépendances PHP

```bash
composer install --no-dev --optimize-autoloader
```

- `--no-dev` : pas de dépendances de développement en production.
- `--optimize-autoloader` : autoloader optimisé.

### 2.3 Fichier d’environnement

```bash
cp .env.example .env
php artisan key:generate
```

Éditer `.env` et renseigner au minimum :

- `APP_NAME`, `APP_URL` (URL finale du site, ex. `https://menupro.ci`)
- `APP_ENV=production`, `APP_DEBUG=false`
- `DB_*` : base MySQL
- `MAIL_*` : SMTP pour les e-mails
- `LYGOS_BASE_URL`, `LYGOS_WEBHOOK_SECRET` si vous utilisez Lygos
- Optionnel : `COMMANDO_*`, `WHATSAPP_*` (voir `.env.example`)

### 2.4 Base de données

```bash
php artisan migrate --force
```

Si vous avez des seeders (rôles, plans, etc.) :

```bash
php artisan db:seed --force
```

### 2.5 Stockage et lien symbolique

```bash
php artisan storage:link
```

Vérifier que `storage` et `bootstrap/cache` sont inscriptibles par le serveur web :

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache   # adapter www-data à l’utilisateur du serveur
```

### 2.6 Build des assets (Vite)

```bash
npm ci
npm run build
```

En production, inutile de lancer `npm run dev`. Les fichiers générés sont dans `public/build`.

### 2.7 Optimisations Laravel

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 3. File d’attente (Queue) et planificateur

MenuPro utilise la queue (ex. `QUEUE_CONNECTION=database`). Il faut un worker qui tourne en continu.

### 3.1 Lancer le worker manuellement (tests)

```bash
php artisan queue:work --tries=3
```

### 3.2 En production : Supervisor (recommandé)

Créer un fichier `/etc/supervisor/conf.d/menupro-worker.conf` (ou équivalent) :

```ini
[program:menupro-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/menupro/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/menupro/storage/logs/worker.log
stopwaitsecs=3600
```

Puis :

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start menupro-worker:*
```

### 3.3 Tâches planifiées (Laravel Scheduler)

Ajouter une entrée crontab pour l’utilisateur du serveur (ex. `www-data` ou l’utilisateur qui exécute PHP) :

```bash
* * * * * cd /var/www/menupro && php artisan schedule:run >> /dev/null 2>&1
```

Vérifier les tâches planifiées :

```bash
php artisan schedule:list
```

---

## 4. Configuration serveur web

### 4.1 Nginx (exemple)

Le point d’entrée doit être `public/` :

```nginx
server {
    listen 80;
    server_name menupro.ci;
    root /var/www/menupro/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
}
```

Penser à activer HTTPS (certificat SSL) et à rediriger HTTP → HTTPS en production.

### 4.2 Apache

DocumentRoot doit pointer vers `public/` et `mod_rewrite` activé. Un fichier `public/.htaccess` est fourni par Laravel.

---

## 5. Checklist avant mise en production

- [ ] `.env` : `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `.env` : `APP_URL` = URL réelle du site (https)
- [ ] `.env` : `APP_KEY` générée (`php artisan key:generate`)
- [ ] Base de données créée et migrations exécutées
- [ ] `php artisan storage:link` exécuté
- [ ] Assets buildés : `npm run build` et présence de `public/build`
- [ ] Permissions : `storage` et `bootstrap/cache` inscriptibles
- [ ] Queue worker lancé (Supervisor ou équivalent)
- [ ] Crontab configuré pour `schedule:run`
- [ ] HTTPS activé et redirection HTTP → HTTPS
- [ ] Secrets (DB, mail, Lygos, etc.) non commités (`.env` dans `.gitignore`)

---

## 6. Mise à jour du déploiement (releases)

### Option A : Script automatique

Après un `git pull`, exécuter :

```bash
cd /var/www/menupro
git pull origin main
chmod +x deploy.sh
./deploy.sh
```

Le script `deploy.sh` enchaîne : Composer, migrations, build assets, caches, redémarrage des workers.

### Option B : Commandes manuelles

```bash
cd /var/www/menupro
git pull origin main

composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci
npm run build

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan queue:restart
```

`queue:restart` demande aux workers de redémarrer après fin du job en cours (utile si vous utilisez Supervisor).

---

## 7. Mode maintenance

Pour mettre le site en maintenance pendant une mise à jour :

```bash
php artisan down
# ... effectuer les mises à jour ...
php artisan up
```

Avec un message personnalisé :

```bash
php artisan down --message="Maintenance prévue. Retour vers 14h."
```

---

## 8. Dépannage rapide

- **Erreur 500** : vérifier les logs `storage/logs/laravel.log`, permissions `storage` et `bootstrap/cache`, et que `APP_DEBUG=false` en production (ne pas exposer les détails d’erreur).
- **Assets 404** : vérifier que `npm run build` a bien été exécuté et que `public/build` existe.
- **Sessions / cache** : après modification de `.env`, exécuter `php artisan config:clear` puis `php artisan config:cache`.
- **Queue qui ne traite pas** : vérifier que le worker tourne (Supervisor ou `queue:work`) et que `QUEUE_CONNECTION` est cohérent dans `.env`.

---

## 9. Résumé des commandes utiles

| Action              | Commande |
|---------------------|----------|
| Générer la clé      | `php artisan key:generate` |
| Migrations          | `php artisan migrate --force` |
| Lien storage        | `php artisan storage:link` |
| Build assets        | `npm run build` |
| Caches              | `php artisan config:cache && php artisan route:cache && php artisan view:cache` |
| Redémarrer la queue | `php artisan queue:restart` |
| Maintenance ON/OFF  | `php artisan down` / `php artisan up` |

Ce guide couvre un déploiement type sur un VPS/serveur. Pour un hébergeur géré (Plesk, cPanel, etc.), adapter les chemins et l’utilisateur exécutant PHP selon la documentation de l’hébergeur.

# Checklist Déploiement Production - MenuPro

## 🔴 AVANT LE DÉPLOIEMENT

### Fichier .env sur le serveur

```env
APP_NAME=MenuPro
APP_ENV=production
APP_DEBUG=false
APP_URL=https://menupro.ci

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=menupro
DB_USERNAME=votre_utilisateur
DB_PASSWORD=votre_mot_de_passe_securise

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=mail.menupro.ci
MAIL_PORT=587
MAIL_USERNAME=no-reply@menupro.ci
MAIL_PASSWORD=votre_mot_de_passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@menupro.ci"
MAIL_FROM_NAME="${APP_NAME}"
```

### Paramètres Système (via /admin/parametres)
- [ ] `app_url` → `https://menupro.ci`
- [ ] `contact_email` → Email de contact réel
- [ ] `contact_phone` → Numéro valide
- [ ] Vérifier le logo et favicon

---

## 🚀 COMMANDES DE DÉPLOIEMENT

```bash
# 1. Installer les dépendances (sans dev)
composer install --no-dev --optimize-autoloader

# 2. Compiler les assets
npm ci
npm run build

# 3. Générer la clé si nouveau serveur
php artisan key:generate

# 4. Lien storage
php artisan storage:link

# 5. Migrations
php artisan migrate --force

# 6. Caches d'optimisation
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Permissions (Linux)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

---

## 🔧 CONFIGURATION SERVEUR

### Nginx (exemple)

```nginx
server {
    listen 80;
    listen 443 ssl http2;
    server_name menupro.ci www.menupro.ci;
    root /var/www/menupro/public;

    ssl_certificate /etc/letsencrypt/live/menupro.ci/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/menupro.ci/privkey.pem;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Supervisor (pour les queues)

```ini
[program:menupro-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/menupro/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/menupro/storage/logs/worker.log
stopwaitsecs=3600
```

### Cron (tâches planifiées)

```cron
* * * * * cd /var/www/menupro && php artisan schedule:run >> /dev/null 2>&1
```

---

## ✅ VÉRIFICATIONS POST-DÉPLOIEMENT

- [ ] Page d'accueil accessible
- [ ] Inscription fonctionne
- [ ] Connexion fonctionne
- [ ] Dashboard Super Admin accessible
- [ ] Dashboard Restaurant accessible
- [ ] Upload d'images fonctionne
- [ ] Emails envoyés correctement
- [ ] Paiements Lygos fonctionnels
- [ ] SSL actif (https)

---

## 📊 FONCTIONNALITÉS DISPONIBLES

### Super Admin (/admin)
- ✅ Dashboard avec stats temps réel
- ✅ Gestion des restaurants
- ✅ Gestion des plans
- ✅ Gestion des abonnements
- ✅ Gestion des utilisateurs
- ✅ Statistiques et analytics
- ✅ Transactions et paiements
- ✅ Journal d'activité
- ✅ Système d'annonces
- ✅ Paramètres système

### Restaurant (/restaurant)
- ✅ Dashboard avec KPIs
- ✅ Gestion des catégories
- ✅ Gestion des plats
- ✅ Gestion des commandes
- ✅ Gestion des stocks
- ✅ Gestion des réservations
- ✅ Avis clients
- ✅ Notifications
- ✅ Paramètres restaurant
- ✅ Abonnement

### Public
- ✅ Page d'accueil
- ✅ Menu public du restaurant
- ✅ Panier et checkout
- ✅ Suivi de commande
- ✅ Formulaire d'avis
- ✅ FAQ
- ✅ Contact
- ✅ Inscription/Connexion

---

## 🔐 SÉCURITÉ

- [ ] APP_DEBUG=false
- [ ] HTTPS activé
- [ ] Headers de sécurité configurés
- [ ] Mots de passe forts
- [ ] Backups automatiques configurés
- [ ] Firewall configuré

---

## 📞 SUPPORT

En cas de problème :
- Logs Laravel : `storage/logs/laravel.log`
- Logs serveur : `/var/log/nginx/error.log`

Créé le : 2026-02-05

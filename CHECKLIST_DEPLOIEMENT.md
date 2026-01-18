# ✅ Checklist de déploiement — MenuPro

**À valider avant de mettre en production.**

---

## 1. Configuration de base

| Étape | Commande / action | Statut |
|-------|-------------------|--------|
| `.env` en production | Copier `.env.example` → `.env`, adapter toutes les variables | ☐ |
| Clé d'application | `php artisan key:generate` | ☐ |
| `APP_ENV` | `APP_ENV=production` | ☐ |
| `APP_DEBUG` | `APP_DEBUG=false` | ☐ |
| `APP_URL` | URL réelle (ex. `https://menupro.ci`) | ☐ |

---

## 2. Base de données

| Étape | Commande / action | Statut |
|-------|-------------------|--------|
| Variables `DB_*` | `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, etc. | ☐ |
| Migrations | `php artisan migrate --force` | ☐ |
| Données initiales | `php artisan db:seed --force` (plans, super-admin) | ☐ |

---

## 3. Fichiers et stockage

| Étape | Commande / action | Statut |
|-------|-------------------|--------|
| Lien storage | `php artisan storage:link` | ☐ |
| Permissions | `storage/` et `bootstrap/cache/` en 775 (ou 755) | ☐ |
| Logs | `storage/logs/` accessible en écriture | ☐ |

---

## 4. Assets (front)

| Étape | Commande / action | Statut |
|-------|-------------------|--------|
| Build production | `npm ci && npm run build` | ☐ |
| Vérifier | Les CSS/JS se chargent sur le site | ☐ |

---

## 5. Paiements Lygos

| Étape | Où / comment | Statut |
|-------|--------------|--------|
| Clé API | Super Admin → Paramètres → Paiement (ou `SystemSetting`) | ☐ |
| Webhook secret | Même écran, `lygos_webhook_secret` | ☐ |
| URL webhook | `https://votre-domaine.com/webhooks/lygos` configurée chez Lygos | ☐ |
| Mode | Test ou Live selon l’environnement | ☐ |

---

## 6. Emails

| Étape | Variable / action | Statut |
|-------|-------------------|--------|
| SMTP | `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, `MAIL_FROM_NAME` | ☐ |
| Ou | SMTP configuré dans Super Admin → Paramètres | ☐ |
| Test | Demande de réinitialisation de mot de passe ou envoi d’un email de test | ☐ |

---

## 7. Files d’attente et tâches planifiées

| Étape | Commande / config | Statut |
|-------|-------------------|--------|
| Queue | `QUEUE_CONNECTION=database` (ou `redis`) | ☐ |
| Table jobs | `php artisan queue:table` puis `php artisan migrate --force` (si `database`) | ☐ |
| Worker | `php artisan queue:work` (ou Supervisor) en arrière-plan | ☐ |
| Cron | `* * * * * cd /chemin/vers/menupro && php artisan schedule:run >> /dev/null 2>&1` | ☐ |

---

## 8. Cache et optimisations

| Étape | Commande | Statut |
|-------|----------|--------|
| Config | `php artisan config:cache` | ☐ |
| Routes | `php artisan route:cache` | ☐ |
| Vues | `php artisan view:cache` | ☐ |

---

## 9. Sécurité et serveur

| Étape | Vérification | Statut |
|-------|--------------|--------|
| HTTPS | Certificat SSL actif, redirection HTTP → HTTPS | ☐ |
| `.env` | Jamais commité, pas accessible depuis le navigateur | ☐ |
| Webhook Lygos | Route `/webhooks/lygos` bien appelée par Lygos (pas de 404) | ☐ |

---

## 10. Premier démarrage

| Étape | Action | Statut |
|-------|--------|--------|
| Super Admin | Se connecter avec le compte créé par `SuperAdminSeeder` | ☐ |
| Paramètres | Vérifier/renseigner : app_name, logo, Lygos, SMTP | ☐ |
| Inscription | Tester une inscription restaurant | ☐ |
| Commande | Tester un parcours : menu → panier → checkout (avec Lygos en test si besoin) | ☐ |

---

## Résumé des commandes (ordre suggéré)

```bash
# Sur le serveur de production
cd /chemin/vers/menupro

composer install --optimize-autoloader --no-dev
npm ci && npm run build

cp .env.example .env
# Éditer .env (APP_*, DB_*, MAIL_*, QUEUE_*, LYGOS_*, etc.)

php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link

php artisan config:cache
php artisan route:cache
php artisan view:cache

# Lancer le worker (à garder actif, ex. via Supervisor)
php artisan queue:work --tries=3
```

**Cron (crontab -e) :**
```
* * * * * cd /chemin/vers/menupro && php artisan schedule:run >> /dev/null 2>&1
```

---

## En cas de problème après déploiement

- Vider les caches : `php artisan config:clear && php artisan route:clear && php artisan view:clear`
- Logs : `storage/logs/laravel.log` et `storage/logs/payments.log`
- Queue : vérifier que `queue:work` tourne et qu’il n’y a pas trop de `failed_jobs`

---

**Une fois cette checklist validée, le déploiement peut être considéré comme bon.**

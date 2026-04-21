# Déployer MenuPro via FTP (sans perdre les données)

Ce guide explique comment mettre à jour ou déployer MenuPro **via FTP** tout en **préservant les données** déjà présentes sur la plateforme en ligne.

---

## Principe : ne jamais écraser ce qui contient les données

| Élément | Action | Raison |
|--------|--------|--------|
| **`.env`** | Ne pas envoyer / ne pas écraser | Contient la config production (DB, clés API). Garder celui du serveur. |
| **Base de données** | Ne pas réinitialiser | Les données (restaurants, commandes, etc.) sont en production. |
| **`storage/app/public/`** | Ne pas écraser | Logos, images des plats, bannières uploadés par les utilisateurs. |
| **`storage/logs/`** | Optionnel | Peut être ignoré ou conservé selon vos besoins. |

---

## 1. Préparer les fichiers en local

À la racine du projet :

```bat
prepare-deploy.bat
```

Ou manuellement :

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

---

## 2. Fichiers à envoyer via FTP

### À envoyer (écrase les anciens fichiers)

- `app/`
- `bootstrap/`
- `config/`
- `database/` (migrations uniquement — ne touche pas aux données existantes)
- `public/` (dont `public/build/` après `npm run build`)
- `resources/`
- `routes/`
- `vendor/` (après `composer install --no-dev`)
- `artisan`
- `composer.json`
- `composer.lock`

### À ne jamais envoyer / écraser

| Fichier ou dossier | Action |
|--------------------|--------|
| `.env` | Ne pas envoyer. Garder celui du serveur. |
| `node_modules/` | Ne pas envoyer (inutile en prod). |
| `storage/app/public/*` | Ne pas écraser. Contient les images uploadées. |
| `storage/logs/*.log` | Optionnel : ne pas envoyer pour garder les logs serveur. |

---

## 3. Ordre recommandé pour l’upload FTP

1. **Backup** : si possible, télécharger une copie du `.env` et de `storage/app/public/` du serveur avant toute modification.
2. Envoyer les dossiers dans cet ordre :
   - `app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`
   - `public/` (y compris `public/build/`)
   - `vendor/`
   - `artisan`, `composer.json`, `composer.lock`
3. **Ne pas toucher** au `.env` existant sur le serveur.
4. **Ne pas supprimer** le contenu de `storage/app/public/` sur le serveur.

---

## 4. Après l’upload : commandes sur le serveur

Si vous avez accès à **SSH** ou au **Terminal cPanel** :

```bash
cd /chemin/vers/votre/projet

# Vider les caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Exécuter uniquement les nouvelles migrations (ne supprime aucune donnée)
php artisan migrate --force
```

`php artisan migrate --force` applique **uniquement les nouvelles** migrations. Les tables et données existantes ne sont pas supprimées.

---

## 5. Sans SSH : uniquement FTP

Si vous n’avez **pas** accès à SSH :

1. Uploadez les fichiers comme indiqué ci-dessus.
2. Les caches seront régénérés au prochain chargement de page (un peu plus lent la première fois).
3. Pour les **migrations** : si de nouvelles colonnes ou tables sont nécessaires, il faudra soit :
   - demander à l’hébergeur d’exécuter `php artisan migrate --force`, ou
   - utiliser un outil type **RunPHP** / script PHP personnalisé (selon l’hébergeur).

---

## 6. Lien symbolique `storage` (si pas déjà fait)

Les images (logos, plats, bannières) sont dans `storage/app/public/`. Pour que `/storage/xxx` fonctionne :

- Si vous utilisez `public_html` : créer un lien symbolique  
  `public_html/storage` → `../VotreDossierProjet/storage/app/public`
- Détails : voir **docs/PREPARATION_HEBERGEMENT.md** section 6.

---

## 7. Résumé : préserver les données

| Action | Risque pour les données |
|--------|-------------------------|
| Envoyer `app/`, `config/`, `resources/`, etc. | Aucun |
| Envoyer `database/migrations/` | Aucun (migrate ajoute seulement) |
| Écraser `.env` | Perte de la config DB → erreurs |
| Écraser `storage/app/public/` | Perte des images uploadées |
| `php artisan migrate --force` | Aucun (ajoute tables/colonnes uniquement) |
| `php artisan migrate:fresh` ou `db:wipe` | Supprime tout — ne jamais faire en prod |

En suivant ce guide, vous mettez à jour le code via FTP sans perdre les données de la plateforme en ligne.

---

## 8. Lien de paiement et erreur 403

### APP_URL (critique pour les paiements)

Le fichier `.env` doit contenir l’URL exacte de votre site en production :

```env
APP_URL=https://menupro.ci
```

**Pourquoi c’est important :** Les URLs de retour après paiement (Lygos, FusionPay) sont générées à partir de `APP_URL`. Si `APP_URL` est incorrect (ex. `http://127.0.0.1:8000`), les clients seront redirigés vers une mauvaise adresse après paiement.

Après modification du `.env` :

```bash
php artisan config:cache
```

### Erreur 403 sur /dashboard/commandes/{id}

Cette URL est utilisée dans l’email « Nouvelle commande » envoyé au restaurant. Elle nécessite une **connexion** avec le compte du restaurant.

- **Non connecté** → redirection vers la page de connexion, puis retour sur la commande après connexion.
- **403 « Accès refusé »** → vous êtes connecté avec un compte qui n’a pas accès à cette commande (autre restaurant, autre rôle). Déconnectez-vous et reconnectez-vous avec le bon compte.

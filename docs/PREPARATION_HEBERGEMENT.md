# Préparer les fichiers pour l’hébergement

À faire **en local** avant d’envoyer le projet sur cPanel (ou tout hébergeur).

---

## 1. Lancer le script de préparation (Windows)

À la racine du projet :

```bat
prepare-deploy.bat
```

Ce script exécute :

- `composer install --no-dev --optimize-autoloader` (dépendances PHP pour la production)
- `npm run build` (compilation des assets dans `public/build/`)

---

## 2. Préparer à la main (si vous préférez)

Dans le dossier du projet, exécutez :

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

---

## 3. Ce qu’il faut envoyer sur le serveur

Inclure **tout** le projet, **sauf** les éléments listés ci‑dessous.

### À inclure (obligatoire)

| Dossier / fichier | Rôle |
|------------------|------|
| `app/` | Code applicatif |
| `bootstrap/` | Démarrage Laravel |
| `config/` | Configuration |
| `database/` | Migrations, seeders |
| `public/` | Point d’entrée web, **dont `public/build/`** (assets compilés) |
| `resources/` | Vues, langues, etc. |
| `routes/` | Routes |
| `storage/` | (dossier vide ou avec sous-dossiers ; permissions à mettre à 755 côté serveur) |
| `vendor/` | Dépendances PHP (généré par `composer install --no-dev`) |
| `artisan` | CLI Laravel |
| `composer.json` | Dépendances PHP |
| `composer.lock` | Versions figées |

### À ne pas envoyer

| Élément | Raison |
|--------|--------|
| `.env` | Contient des infos locales / secrets ; à recréer sur le serveur à partir de `.env.example` |
| `node_modules/` | Très volumineux ; inutile en production (seul `public/build/` compte) |
| `.git/` | Optionnel : pas nécessaire pour faire tourner le site |
| `storage/logs/*.log` | Logs locaux |
| `storage/framework/cache/data/*` | Cache local |
| `public/hot` | Fichier de dev Vite |
| Fichiers IDE (`.idea/`, `.vscode/`, etc.) | Inutiles en production |

---

## 4. Créer une archive pour l’upload

Sous Windows, vous pouvez créer un zip en **excluant** les dossiers/fichiers listés ci‑dessus.

Exemple avec PowerShell (à lancer à la racine du projet, ex. `C:\laragon\www\MenuPro`) :

```powershell
# Exemple : créer MenuPro-deploy.zip en excluant .env, node_modules, .git, logs
$exclude = @('.env', 'node_modules', '.git', 'storage\logs', 'public\hot', '.idea', '.vscode')
Compress-Archive -Path * -DestinationPath ..\MenuPro-deploy.zip -Force
# Note : Compress-Archive n'exclut pas facilement des dossiers. Pour une vraie exclusion, utilisez 7-Zip ou WinRAR, ou supprimez temporairement node_modules et .git avant de zipper.
```

En pratique, le plus simple est souvent :

1. Lancer `prepare-deploy.bat` (pour avoir `vendor/` et `public/build/`).
2. Supprimer ou ne pas inclure dans le zip : `node_modules`, `.git`, `.env`.
3. Zipper le reste (ou utiliser le Gestionnaire de fichiers cPanel / FTP pour uploader tout sauf ces dossiers).

---

## 5. Sur le serveur après l’upload

- Créer le fichier **`.env`** à partir de `.env.example` et remplir au moins :  
  `APP_KEY`, `APP_URL`, `DB_*`, options mail / paiement si besoin.
- Permissions : `storage` et `bootstrap/cache` en **755** (écriture pour le serveur).
- Lancer (depuis la racine du projet sur le serveur) :  
  `php artisan config:cache`  
  `php artisan route:cache`  
  `php artisan view:cache`  
  `php artisan migrate --force`

Détails complets : **docs/DEPLOIEMENT_CPANEL.md**.

---

## 6. Afficher les images (structure public_html + Menupro)

Si vous avez **copié le contenu de `public/` dans `public_html/`** et que le reste du projet est dans un dossier (ex. **Menupro**) à la racine de l’hébergeur, les **images uploadées** (logos, plats, etc.) sont dans `Menupro/storage/app/public/`. Laravel génère des URLs du type `/storage/system/xxx.png`. Pour que ces URLs fonctionnent, le serveur doit servir le contenu de `storage/app/public` sous le chemin `/storage/`.

### Créer le lien symbolique sur le serveur

Depuis la **racine de l’hébergeur** (là où se trouvent `public_html` et `Menupro`) :

**Linux / SSH :**
```bash
ln -s ../Menupro/storage/app/public public_html/storage
```

Remplacez `Menupro` par le **nom exact** du dossier de votre projet si besoin.

**Sans SSH (cPanel, etc.) :**

1. Dans le **Gestionnaire de fichiers**, aller dans `public_html`.
2. Créer un **lien symbolique** (souvent « Symbolic Link » ou « Lien ») :
   - Nom du lien : `storage`
   - Cible : `../Menupro/storage/app/public` (ou le chemin complet vers `storage/app/public` du projet).

Après ça, les URLs `https://votredomaine.com/storage/...` afficheront bien les images stockées dans `storage/app/public/`.

### Vérification

- Les **fichiers statiques** dans `public/images/` (ou `public/build/`) sont déjà dans `public_html/` → pas de changement.
- Seules les **images gérées par Laravel** (Storage, uploads) ont besoin du lien `public_html/storage` → `Menupro/storage/app/public`.

# MenuPro — Documentation Technique Complète

> Dernière mise à jour : 2026-07-06

---

## 1. Vue d'ensemble

### Objectif

MenuPro est une plateforme SaaS multi-tenant de gestion de restaurants ciblant l'Afrique de l'Ouest (devise FCFA). Elle couvre la création de menus numériques, la prise de commandes en ligne (QR code table, commande emporter/livraison), les paiements mobiles locaux, la gestion de stock, la livraison avec suivi GPS temps réel, et un CRM interne pour les équipes commerciales.

### Diagramme d'architecture (ASCII)

```
┌─────────────────────────────────────────────────────────────────────┐
│                          INTERNET / CLIENTS                          │
│  Navigateur (Client)  ·  App mobile (API)  ·  Tablette KDS/Caisse  │
└────────────────────────────────┬────────────────────────────────────┘
                                 │ HTTPS
┌────────────────────────────────▼────────────────────────────────────┐
│              SERVEUR WEB (Apache/Nginx + PHP 8.2 FPM)               │
│                                                                      │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │                   Laravel 12 Application                     │    │
│  │                                                               │    │
│  │  ┌──────────────┐  ┌──────────────┐  ┌──────────────────┐   │    │
│  │  │  Web Routes  │  │  API v1      │  │  Webhook Routes  │   │    │
│  │  │  (Livewire 4)│  │  (Sanctum)   │  │  Wave/MoneyFusion│   │    │
│  │  └──────┬───────┘  └──────┬───────┘  └────────┬─────────┘   │    │
│  │         │                 │                    │              │    │
│  │  ┌──────▼─────────────────▼────────────────────▼──────────┐  │    │
│  │  │              Controllers / Livewire Components          │  │    │
│  │  └──────────────────────────┬─────────────────────────────┘  │    │
│  │                             │                                 │    │
│  │  ┌──────────────┐  ┌────────▼────────┐  ┌──────────────┐    │    │
│  │  │   Services   │  │     Models      │  │    Events    │    │    │
│  │  │  FcmService  │  │  (Multi-tenant) │  │  (Broadcast) │    │    │
│  │  │  WaveService │  └────────┬────────┘  └──────┬───────┘    │    │
│  │  └──────────────┘           │                  │             │    │
│  └─────────────────────────────│──────────────────│─────────────┘    │
│                                │                  │                  │
│  ┌─────────────┐  ┌────────────▼──┐  ┌──────────▼──────────────┐   │
│  │  Queue      │  │  MySQL 8.0+   │  │  Laravel Reverb         │   │
│  │  Workers    │  │  (Base de     │  │  (WebSocket Server       │   │
│  │  (database) │  │   données)    │  │   port 8080)             │   │
│  └─────────────┘  └───────────────┘  └─────────────────────────┘   │
└─────────────────────────────────────────────────────────────────────┘
         │                                        │
         │ HTTP                                   │ WSS
┌────────▼────────┐                    ┌──────────▼──────────────┐
│  Firebase FCM   │                    │  Navigateurs / Apps     │
│  (Push notifs)  │                    │  (laravel-echo + pusher)│
└─────────────────┘                    └─────────────────────────┘
```

### Principes clés

- **Multi-tenant par colonne** : chaque entité (`orders`, `dishes`, `categories`, etc.) porte un `restaurant_id`. Le scope est injecté par le middleware `SetRestaurantScope` à partir du paramètre de route ou de `auth()->user()->restaurant_id`.
- **Temps réel hybride** : Laravel Reverb (WebSocket) pour les dashboards restaurant et le KDS ; polling AJAX (toutes les 10 s) comme fallback.
- **Paiements locaux** : Wave CI (mobile money CI), MoneyFusion, Lygos, et cash on delivery.
- **Push notifications** : Firebase FCM HTTP v1 via JWT RS256 (legacy API désactivée depuis juin 2024).

---

## 2. Stack Technique

### Backend

| Composant | Version | Rôle |
|---|---|---|
| PHP | ^8.2 | Moteur applicatif |
| Laravel | ^12.0 | Framework principal |
| Livewire | ^4.0 | Composants Blade réactifs (SSR + Alpine.js) |
| Laravel Reverb | ^1.10 | Serveur WebSocket natif Laravel |
| Laravel Sanctum | ^4.3 | Authentification API par token Bearer |
| Laravel Tinker | ^2.10.1 | REPL interactif |
| barryvdh/laravel-dompdf | ^3.1 | Génération PDF (carte agent, reçus) |
| maatwebsite/excel | ^3.1 | Export CSV/Excel (clients, abonnements) |
| simplesoftwareio/simple-qrcode | ^4.2 | Génération QR codes SVG/PNG |

### Frontend

| Composant | Version | Rôle |
|---|---|---|
| Vite | ^7.0.7 | Bundler assets |
| TailwindCSS | ^4.0 | Framework CSS utilitaire |
| Alpine.js | ^3.15.4 | Réactivité DOM côté client |
| @alpinejs/focus | ^3.15.4 | Plugin gestion du focus |
| Chart.js | ^4.5.1 | Graphiques analytics |
| driver.js | ^1.4.0 | Onboarding / tutoriels guidés |
| laravel-echo | ^2.1.0 | Client WebSocket (Reverb) |
| pusher-js | ^8.4.0 | Transport WebSocket (utilisé par Echo) |
| firebase | ^12.15.0 | SDK Firebase (FCM push, Service Worker) |
| axios | ^1.11.0 | Client HTTP AJAX |

### Base de données

| Composant | Détail |
|---|---|
| Moteur | MySQL 8.0+ (utf8mb4_unicode_ci) |
| Driver Laravel | `mysql` |
| Migrations | ~109 fichiers, historique depuis 2024-01-01 |
| Index performance | Ajoutés en 2026-06 (commandes, livraisons, agents) |
| Cache | `database` driver (table `cache`) ou Redis (optionnel) |
| Queue | `database` driver (table `jobs`) |
| Sessions | `file` ou `database`, chiffrées, SameSite=strict |

### Paiements

| Fournisseur | Usage | Intégration |
|---|---|---|
| Wave CI | Commandes clients + retraits restaurants | API REST + Webhooks HMAC (header `Wave-Signature`) |
| MoneyFusion | Abonnements restaurants | API REST + Webhooks |
| Lygos | Commandes clients (par restaurant, clé API propre) | API REST + Webhooks HMAC (`LYGOS_WEBHOOK_SECRET`) |
| Cash on delivery | Paiement à la livraison | Logique interne, pas d'API externe |
| Firebase FCM | Push notifications (commandes, livraisons) | HTTP v1 API, OAuth2 JWT RS256 |

### Infrastructure

| Composant | Détail |
|---|---|
| Hébergement | cPanel / shared hosting (deploy.sh) ou VPS |
| URL production | `https://menupro.ci` |
| WebSocket | Reverb sur port 8080, scheme HTTPS |
| Stockage fichiers | Disk `local` (par défaut) ou S3 (optionnel) |
| Mail | SMTP (configurable), port 587 TLS |
| CORS | Origines autorisées : `menupro.ci`, `delivery.menupro.ci`, `driver.menupro.ci`, `localhost:3000/3001` |

---

## 3. Installation & Setup Local

### Prérequis

- PHP 8.2+ avec extensions : `pdo_mysql`, `openssl`, `mbstring`, `xml`, `gd`, `zip`, `bcmath`
- MySQL 8.0+
- Composer 2.x
- Node.js 20+ / npm 10+
- Git

### Étapes

```bash
# 1. Cloner le dépôt
git clone <repo-url> MenuPro
cd MenuPro

# 2. Installer les dépendances PHP
composer install

# 3. Copier et configurer le fichier d'environnement
cp .env.example .env
# Editer .env : DB_DATABASE, DB_USERNAME, DB_PASSWORD, APP_URL, etc.

# 4. Générer la clé d'application
php artisan key:generate

# 5. Créer la base de données et lancer les migrations
php artisan migrate

# (Optionnel) Charger les données de démonstration
php artisan db:seed

# 6. Installer les dépendances frontend
npm install

# 7. Compiler les assets (développement)
npm run dev

# OU compiler pour la production
npm run build

# 8. Lancer le serveur de développement complet (all-in-one)
composer dev
# Lance en parallèle : php artisan serve, queue:listen, pail (logs), vite
```

Le script `composer dev` utilise `concurrently` pour démarrer simultanément :
- `php artisan serve` — serveur HTTP sur `localhost:8000`
- `php artisan queue:listen --tries=1` — worker de files d'attente
- `php artisan pail --timeout=0` — viewer de logs en temps réel
- `npm run dev` — serveur Vite avec HMR

### Configuration Reverb (WebSocket)

```bash
# Démarrer le serveur WebSocket
php artisan reverb:start

# En production avec restart automatique
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### Création d'un Super Admin

```bash
php artisan tinker
# Dans Tinker :
$user = \App\Models\User::create([...]);
$user->role = \App\Enums\UserRole::SUPER_ADMIN;
$user->save();
```

---

## 4. Variables d'Environnement

### Application

| Variable | Description | Exemple |
|---|---|---|
| `APP_NAME` | Nom affiché | `MenuPro` |
| `APP_ENV` | Environnement | `production` / `local` |
| `APP_KEY` | Clé de chiffrement Laravel (générer avec `key:generate`) | `base64:...` |
| `APP_DEBUG` | Affichage des erreurs | `false` (prod) |
| `APP_URL` | URL complète avec schéma | `https://menupro.ci` |
| `APP_LOCALE` | Langue par défaut | `fr` |
| `BCRYPT_ROUNDS` | Coût de hachage bcrypt | `12` |

### Base de données

| Variable | Description | Exemple |
|---|---|---|
| `DB_CONNECTION` | Driver | `mysql` |
| `DB_HOST` | Hôte MySQL | `127.0.0.1` |
| `DB_PORT` | Port | `3306` |
| `DB_DATABASE` | Nom de la base | `menupro` |
| `DB_USERNAME` | Utilisateur | `root` |
| `DB_PASSWORD` | Mot de passe | `secret` |

### Session & Cache

| Variable | Description | Exemple |
|---|---|---|
| `SESSION_DRIVER` | Stockage sessions | `file` / `database` |
| `SESSION_LIFETIME` | Durée (minutes) | `120` |
| `SESSION_ENCRYPT` | Chiffrement sessions | `true` |
| `SESSION_SECURE_COOKIE` | Cookie HTTPS uniquement | `true` |
| `SESSION_SAME_SITE` | Protection CSRF | `strict` |
| `CACHE_STORE` | Driver cache | `database` / `redis` |
| `QUEUE_CONNECTION` | Driver queues | `database` |

### WebSocket Reverb

| Variable | Description | Exemple |
|---|---|---|
| `BROADCAST_CONNECTION` | Driver broadcast | `reverb` |
| `REVERB_APP_ID` | Identifiant app Reverb | `menupro` |
| `REVERB_APP_KEY` | Clé publique | `menupro-reverb-key` |
| `REVERB_APP_SECRET` | Secret privé | `menupro-reverb-secret` |
| `REVERB_HOST` | Hôte public | `menupro.ci` |
| `REVERB_PORT` | Port public | `8080` |
| `REVERB_SCHEME` | Protocole public | `https` |
| `REVERB_SERVER_HOST` | Interface d'écoute serveur | `0.0.0.0` |
| `REVERB_SERVER_PORT` | Port d'écoute serveur | `8080` |

### Mail

| Variable | Description | Exemple |
|---|---|---|
| `MAIL_MAILER` | Driver mail | `smtp` |
| `MAIL_HOST` | Serveur SMTP | `smtp.mailgun.org` |
| `MAIL_PORT` | Port | `587` |
| `MAIL_USERNAME` | Identifiant | `postmaster@...` |
| `MAIL_PASSWORD` | Mot de passe | `secret` |
| `MAIL_ENCRYPTION` | Chiffrement | `tls` |
| `MAIL_FROM_ADDRESS` | Expéditeur | `no-reply@menupro.ci` |

### Paiements

| Variable | Description | Exemple |
|---|---|---|
| `WAVE_API_KEY` | Clé API Wave CI (Bearer) | `wave_sn_...` |
| `WAVE_SIGNING_SECRET` | Secret HMAC webhooks Wave | `wh_...` |
| `MONEYFUSION_API_URL` | URL API MoneyFusion | `https://...` |
| `MONEYFUSION_API_KEY` | Clé API MoneyFusion | `mf_...` |
| `LYGOS_BASE_URL` | URL API Lygos | `https://api.lygosapp.com/v1` |
| `LYGOS_WEBHOOK_SECRET` | Secret HMAC webhooks Lygos | `lygos_...` |

### Module Commando (Agents)

| Variable | Description | Défaut |
|---|---|---|
| `COMMANDO_MONTHLY_TARGET` | Objectif mensuel installations | `10` |
| `COMMANDO_COMMISSION_CENTS_FIRST_PAYMENT` | Commission 1er paiement (centimes FCFA) | `500000` (5000 FCFA) |
| `COMMANDO_COMMISSION_ONLY_FIRST_PAYMENT` | Une seule commission par restaurant | `true` |

### Notifications & Intégrations

| Variable | Description | Exemple |
|---|---|---|
| `WHATSAPP_ENABLED` | Activer WhatsApp (agents Commando) | `false` |
| `WHATSAPP_API_URL` | URL API WhatsApp Business | `https://...` |
| `WHATSAPP_API_KEY` | Clé API | `...` |
| `WHATSAPP_PHONE_ID` | ID numéro WhatsApp | `...` |

> Firebase (FCM) : les variables `firebase_project_id`, `firebase_service_account_json` (JSON du Service Account) et `firebase_server_key` sont stockées dans la table `system_settings` (via l'interface Super Admin), pas dans `.env`.

---

## 5. Architecture Multi-Tenant

### Principe

MenuPro utilise une architecture **multi-tenant par colonne** (shared database, shared schema). Toutes les tables liées à un restaurant portent un champ `restaurant_id` qui sert de clé de partition.

Il n'existe pas de trait `BelongsToRestaurant` générique dans le code source. L'isolation est assurée par deux mécanismes complémentaires :

### Middleware `SetRestaurantScope`

Fichier : `app/Http/Middleware/SetRestaurantScope.php`

Ce middleware est appliqué à tous les groupes de routes restaurant (middleware alias `set.restaurant.scope`). Il détermine le restaurant courant selon cette priorité :

1. **Paramètre de route `slug`** — pour les pages publiques (`/r/{slug}/...`)
2. **Paramètre de route `restaurant`** — pour les routes Super Admin
3. **`auth()->user()->restaurant_id`** — pour les utilisateurs connectés (admins, employés)

Il stocke l'ID dans `session('current_restaurant_id')` et partage les variables `$restaurant` et `$subscription` avec toutes les vues Blade.

### Filtrage dans les Controllers/Livewire

Les controllers et composants Livewire filtrent **explicitement** par `restaurant_id` :

```php
// Exemple typique dans un controller restaurant
$orders = Order::where('restaurant_id', auth()->user()->restaurant_id)
    ->latest()
    ->paginate(20);
```

### Super Admin : accès cross-tenant

Le Super Admin (`role = super_admin`) contourne les scopes grâce à `User::canAccessRestaurant()` qui retourne `true` pour tout `restaurantId`. La fonction `impersonate` lui permet de naviguer dans l'interface d'un restaurant spécifique.

### Limitations en ligne de commande (CLI)

Les commandes Artisan ne passent pas par le middleware `SetRestaurantScope`. Les jobs en queue doivent toujours recevoir explicitement le `restaurant_id` dans leur payload pour éviter d'opérer sur toutes les données.

---

## 6. Rôles & Permissions

### Enum `UserRole`

Fichier : `app/Enums/UserRole.php`

| Valeur (`role`) | Label UI | Dashboard | Middleware |
|---|---|---|---|
| `super_admin` | Manager | `/admin` | `super.admin` (`EnsureSuperAdmin`) |
| `restaurant_admin` | Administrateur Restaurant | `/dashboard` | `auth` + `set.restaurant.scope` |
| `employee` | Employé | `/dashboard` | `auth` + `set.restaurant.scope` |
| `commando_agent` | Ambassadeur | `/commando/dashboard` (redirect `/crm`) | `commando.agent` (`EnsureCommandoAgent`) |
| `commercial` | Ambassadeur | `/crm` | `crm.role` (`EnsureCrmRole`) |
| `technician` | Technicien | `/crm` | `crm.role` (`EnsureCrmRole`) |
| `team_leader` | Team Leader | `/crm` | `crm.role` (`EnsureCrmRole`) |
| `customer` | Client | N/A (API uniquement) | `auth:sanctum` |
| `delivery_driver` | Livreur | N/A (API uniquement) | `auth:sanctum` + `delivery.driver` |

### Permissions fonctionnelles

| Action | super_admin | restaurant_admin | employee |
|---|---|---|---|
| Gérer le menu (plats, catégories) | Oui | Oui | Non (lecture seule) |
| Gérer les commandes | Oui | Oui | Oui |
| Accéder au POS | Oui | Oui | Oui |
| Analytics & rapports | Oui | Oui (plan Pro+) | Non |
| Stock (feature `stock`) | Oui | Oui (plan Pro+) | Partiel |
| Gérer l'équipe | Oui | Oui | Non |
| Paramètres restaurant | Oui | Oui | Non |
| Générer token KDS | Oui | Oui | Non |
| Impersonifier un restaurant | Oui | Non | Non |

Le middleware `feature:analytics` et `feature:stock` (`CheckPlanFeature`) vérifie le plan d'abonnement actif du restaurant avant d'autoriser l'accès.

---

## 7. Modules Fonctionnels

### 7.1 Restaurant Admin

Dashboard accessible à `/dashboard` (middleware `auth`, `verified`, `set.restaurant.scope`).

#### Menu

- **Catégories** : CRUD + réordonnancement drag-and-drop (admin uniquement)
- **Plats** : CRUD + toggle disponibilité + mise en avant (`featured`) + variantes (`dish_variants`) + association ingrédients pour le suivi de stock
- **QR Codes** : génération QR code statique (URL menu `/r/{slug}`) et QR codes par table numérotée ; téléchargement SVG/PNG ; carte sociale téléchargeable

#### Commandes

Plusieurs vues de gestion :
- **Liste Livewire** (`/dashboard/commandes`) — tableau paginé avec filtres
- **Kanban board** (`/dashboard/commandes/kanban`) — colonnes par statut, polling 30 s
- **Rush mode** (`/dashboard/commandes/rush`) — vue opérationnelle temps réel
- **Détail commande** avec impression et modification des items (ajout/suppression/modification jusqu'au statut `preparing`)
- **Remboursements** : via `orders/{order}/refund`

#### POS (Point of Sale)

Accessible à `/dashboard/pos` — interface caisse optimisée pour tablette/écran tactile, créée avec Livewire.

#### Réservations

CRUD réservations, mise à jour de statut, accessible aux admins restaurant.

#### Avis clients

Modération des avis déposés post-commande (lien par token de suivi).

#### Analytics (plan Pro+)

- Statistiques ventes, revenus, commandes
- Codes promo
- Rapports téléchargeables (CSV/Excel via Maatwebsite\Excel)
- Dépenses
- Rentabilité par plat (`DishProfitability`)

#### Stock (plan Pro+)

- Ingrédients + mouvements (entrée, sortie, ajustement, gaspillage)
- Catégories d'ingrédients + fournisseurs + liaisons plat-ingrédient
- Stock journalier + mise à jour en masse
- Alertes de stock bas
- Rapport stock (téléchargeable)

#### Équipe

Gestion des utilisateurs rattachés au restaurant (rôle `employee`), via Livewire.

#### Abonnement

- Consultation plan actuel, changement de plan
- Factures téléchargeables
- Conversion d'un essai gratuit

### 7.2 Paiements

#### Wave CI

- Utilisé pour les commandes clients et les retraits (pay-out) restaurants
- Auth : header `Authorization: Bearer {WAVE_API_KEY}`
- Webhooks : signature HMAC vérifiée via `Wave-Signature` (secret `WAVE_SIGNING_SECRET`)
- Callback client : `/api/v1/client/payment/success` et `/api/v1/client/payment/error`
- Webhook server-to-server : `POST /webhooks/wave`
- Variables par restaurant : `wave_merchant_id`, `wave_business_phone`, `wave_business_enabled`

#### MoneyFusion / FusionPay

- Utilisé pour les abonnements restaurants
- Webhook : `POST /webhooks/moneyfusion`
- Variables : `MONEYFUSION_API_URL`, `MONEYFUSION_API_KEY`

#### Lygos

- Paiement alternatif par restaurant (chaque restaurant configure sa propre clé)
- Variables par restaurant : `lygos_api_key`, `lygos_api_secret`, `lygos_enabled`
- Webhook HMAC : `LYGOS_WEBHOOK_SECRET`

#### Cash on delivery

- Option par restaurant (`cash_on_delivery = true`)
- Pas d'API externe ; la commande passe directement en `pending_payment`

#### Wallets restaurants

Table `restaurant_wallets` : solde en FCFA, gestion des retraits (`payout_transactions`), auto-payout configurable. Accès via API interne Sanctum (`/api/wallet/{restaurantId}/balance`, `/api/payouts/request`).

#### Flux de paiement (commande client)

```
Client commande
    └─> POST /api/v1/client/orders (crée Order en draft)
    └─> POST /api/v1/client/payment/{orderId}/initiate
            └─> WaveService / LygosService génère lien de paiement
    └─> Redirection vers page de paiement fournisseur
    └─> Callback webhook (Wave ou Lygos) → OrderStatus: PAID
    └─> Event OrderStatusChanged (broadcast WebSocket)
    └─> Notification FCM push au restaurant
```

### 7.3 Livraison Plateforme

#### Livreurs

Modèle `DeliveryDriver` (table `delivery_drivers`) : lié à un `User` (role `delivery_driver`). Champs : véhicule, plaque, CNI, permis, statut de vérification, disponibilité, position GPS (lat/lon), gains totaux, rating, token FCM.

Statuts de vérification : `pending`, `approved`, `rejected`, `suspended`.

#### Assignation GPS (Haversine)

Lorsqu'une commande est prête pour la livraison, le système recherche les livreurs disponibles dans un rayon configurable en utilisant la formule de Haversine pour calculer la distance GPS.

#### Suivi temps réel

- **WebSocket** : l'event `DriverLocationUpdated` est broadcasté sur un channel dédié.
- **API REST livreur** : `PATCH /api/v1/driver/location` (rate limit : 30 req/min, middleware `throttle:api.driver.location`)
- **Statuts livreur** : `pending` → `assigned` → `picked_up` → `delivered`

#### Gains livreurs

Table `driver_earnings`, résumé via `GET /api/v1/driver/earnings`, historique via `/earnings/history`, paiement à la demande (max 3/jour) via `POST /api/v1/driver/earnings/payout`.

### 7.4 KDS (Kitchen Display System)

Le KDS est un écran dédié en cuisine, accessible sans authentification, sécurisé par un token aléatoire (32 caractères) généré par l'admin restaurant.

#### Accès

```
URL : /cuisine/{token}
```

Le token est stocké dans `restaurants.kitchen_token`. L'admin génère/régénère via `POST /dashboard/cuisine/generate-token`.

#### Fonctionnement

1. `GET /cuisine/{token}` — charge la vue Blade avec les commandes actives (statuts `paid`, `confirmed`, `preparing`, `ready`) en JSON initial
2. `GET /cuisine/{token}/data` — endpoint polling AJAX retournant les commandes + compteurs par colonne
3. `POST /cuisine/{token}/orders/{order}/status` — met à jour le statut d'une commande depuis la cuisine

#### Colonnes KDS

| Colonne | Statuts affichés |
|---|---|
| Nouvelles | `paid`, `confirmed` |
| En préparation | `preparing` |
| Prêtes | `ready` |

#### Cycle de statut commandes cuisine

```
PAID / CONFIRMED  →  PREPARING  →  READY  →  DELIVERING / COMPLETED
```

La synthèse vocale est activée côté frontend (Web Speech API) pour annoncer les nouvelles commandes.

### 7.5 Agents Commando

Programme d'ambassadeurs terrain qui recrutent des restaurants pour MenuPro.

#### Inscription (flux en 2 étapes)

1. **Étape 1** (`/commando/inscription`) — Livewire `RegisterStep1` : nom, email, téléphone, ville, mot de passe. Crée un `User` (role `commando_agent`) + un `CommandoAgent` en statut `pending`.
2. **Étape 2** (`/commando/inscription/verification`) — Livewire `RegisterStep2` : upload pièce d'identité (CNI recto/verso).

#### Approbation

Le Super Admin valide (`approve`) ou rejette (`reject`) l'agent depuis `/admin/commando/agents`. Après approbation, un lien de bienvenue (`/commando/bienvenue?token=...`) est envoyé pour définir le mot de passe définitif et (optionnellement) via WhatsApp.

#### Carte digitale Agent

URL : `/commando/carte` — carte numérique personnalisée avec QR code de vérification publique (`/verify/{uuid}`). Téléchargeable en PDF via DomPDF.

#### Commissions

- Déclenchée automatiquement au 1er paiement d'un restaurant parrainé
- Montant configurable : `COMMANDO_COMMISSION_CENTS_FIRST_PAYMENT` (défaut 5000 FCFA)
- Option `COMMANDO_COMMISSION_ONLY_FIRST_PAYMENT=true` : une seule commission par restaurant
- Table `commando_commission_transactions` : historique des transactions
- Wallet intégré : solde, demandes de retrait, validation Super Admin

#### Grades

Basés sur le nombre d'installations actives mensuelles (`COMMANDO_MONTHLY_TARGET`).

### 7.6 CRM

Outil interne pour les équipes commerciales et techniques de MenuPro.

#### Rôles CRM

- **Commercial** (`commercial`) : gère les leads, suit le pipeline
- **Technicien** (`technician`) : réalise les installations
- **Team Leader** (`team_leader`) : supervise une équipe, accès aux rapports de performance

#### Pipeline Leads (Kanban)

Statuts ordonnés (avec transitions définies dans `LeadStatus::canTransitionTo()`) :

```
Nouveau → Contacté → Démonstration → Relance → Signature → Installation → Actif
                                                                          ↘ Perdu (possible à toutes étapes)
```

Vue Kanban avec colonnes glissables ; chaque lead porte un `score`, une `source`, l'agence/ville, le plan d'abonnement cible, la date de prochaine action.

#### Installations

Table `crm_installations` : liée à un lead + un technicien. Statuts : planifiée, en cours, terminée, annulée.

#### Équipes

Table `crm_teams` : chaque équipe a un leader (`leader_id`) et des membres (pivot `crm_team_members` avec `role_in_team`).

#### Grades CRM

Table `crm_grades` : grade actuel de l'utilisateur (Bronze, Silver, Gold, etc.) calculé selon les performances.

#### Commissions CRM

Table `crm_commissions` : commissions de première installation, récurrentes mensuelles, paliers technicien. Wallet (`crm_wallets`) + demandes de retrait (`crm_withdrawals`).

#### Performance Snapshots

Table `crm_performance_snapshots` : captures mensuelles des métriques par utilisateur CRM.

### 7.7 Super Admin

Accessible à `/admin` (middleware `super.admin`, `auth`, `verified`).

#### Gestion restaurants

- Liste, détail, validation/rejet/suspension/réactivation
- Vérification (`verified_at`) et toggle mode démo
- Impersonation : `POST /admin/restaurants/{restaurant}/impersonate`
- Extension d'abonnement manuelle
- Export CSV

#### Gestion utilisateurs

CRUD complet, suspension, réinitialisation mot de passe, changement de rôle.

#### Plans d'abonnement

CRUD plans, réordonnancement, gestion des fonctionnalités (`has_analytics`, `has_stock_management`, etc.).

#### Finances

- Wallets restaurants, retraits (pay-out)
- Commissions CRM
- Transactions globales avec export

#### Statistiques

- Revenus, croissance, commandes par période
- Export CSV/Excel

#### Annonces

Création et envoi d'annonces push/email à tous les restaurants.

#### Paramètres système

Interface de gestion des `system_settings` (Firebase, paramètres globaux, branding).

#### Livraisons

- Dashboard livreurs (validation, suspension)
- Villes de livraison + zones avec toggle
- Dashboard livraisons en temps réel (polling)

#### Feed commandes live

`GET /admin/commandes-live` avec données via polling `GET /admin/api/live-orders`.

---

## 8. Sécurité

### Headers HTTP

Middleware `SecurityHeaders` (`app/Http/Middleware/SecurityHeaders.php`) appliqué globalement :

| Header | Valeur |
|---|---|
| `X-Frame-Options` | `SAMEORIGIN` |
| `X-Content-Type-Options` | `nosniff` |
| `X-XSS-Protection` | `1; mode=block` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` |
| `Permissions-Policy` | `geolocation=(self), microphone=(), camera=(), payment=(), usb=()` |
| `Strict-Transport-Security` | `max-age=31536000; includeSubDomains` |
| `Content-Security-Policy` | `default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' [Google, CDN...]; img-src 'self' data: blob: https:; connect-src 'self' wss: https:; frame-ancestors 'self'` |

L'API dispose d'un middleware séparé `ApiSecurityHeaders`.

### Authentification

- **Web** : sessions Laravel (cookie chiffré, SameSite=strict, Secure=true)
- **API** : Laravel Sanctum (Bearer token), middleware `auth:sanctum`
- **KDS et livreur web** : token aléatoire dans l'URL (sans session)

### CSRF

Protection CSRF native Laravel pour toutes les routes POST/PUT/DELETE web. Endpoint `/csrf-token` pour rafraîchissement côté client (évite les erreurs 419).

### Multi-tenancy

- Le champ `role` et `restaurant_id` des utilisateurs sont dans `$guarded` (protection contre le mass-assignment)
- `User::belongsToRestaurant()` fait un cast entier explicite pour éviter les comparaisons type `"1" == 1`
- Le Super Admin est le seul rôle à pouvoir accéder aux données cross-tenant

### Webhooks HMAC

Les webhooks Wave et MoneyFusion vérifient la signature cryptographique avant tout traitement. Les routes webhook sont déclarées `withoutMiddleware(['web'])` pour désactiver la vérification CSRF (les appels viennent de serveurs tiers).

### Rate Limiting

Throttling configuré par contexte :

| Contexte | Limite |
|---|---|
| Login web | 5 req/min (`throttle:5,1`) |
| Register web | 3 req/min (`throttle:3,1`) |
| API auth (login) | `throttle:api.auth` |
| API register | `throttle:api.register` |
| API commandes client | `throttle:api.orders` |
| API paiement | `throttle:api.payment` |
| API position livreur | `throttle:api.driver.location` (30 req/min) |
| API public (restaurants) | `throttle:api.public` |
| API Super Admin | `throttle:api.admin` |
| API payout livreur | `throttle:api.payout` (max 3/jour) |
| Kanban/Rush data | `throttle:30,1` |

### Sanitisation API

Middleware `SanitizeApiInput` (`api.sanitize`) : nettoie les entrées de l'API v1 pour prévenir les injections.

---

## 9. Performance

### Index base de données

Les migrations de performance (`2026_06_16_100000_add_performance_indexes.php` et `2026_06_21_100000_add_scalability_indexes.php`) ajoutent des index composites sur les tables critiques :
- `orders(restaurant_id, status, created_at)` — listes de commandes filtrées
- `orders(restaurant_id, created_at)` — analytics temporelles
- `delivery_drivers(is_active, is_available, latitude, longitude)` — recherche livreurs proches
- `commando_agents`, `crm_leads`, etc.

### Cache SystemSetting

`SystemSetting::get()` met en cache chaque clé pendant **300 secondes** dans le store `cache` configuré. Invalider manuellement avec `Cache::forget('system_setting_' . $key)` après une mise à jour.

### Eager Loading

Les relations fréquemment utilisées sont chargées en eager loading (`with()`) dans les controllers pour éviter le problème N+1 :

```php
// Exemple KDS
Order::where('restaurant_id', $restaurant->id)
    ->with('items.dish')
    ->get();
```

### Pagination

Toutes les listes utilisent `paginate()` (défaut 20-25 éléments par page). Les exports CSV utilisent des `chunk()` pour éviter les Out of Memory.

### Queue Workers

Les jobs lourds (envois email, notifications FCM batch, calculs de commissions) sont délégués à la queue (`database` driver). En production, utiliser un supervisor process pour maintenir le worker actif.

---

## 10. API v1

### Base URL

```
https://menupro.ci/api/v1
```

### Authentification

```
Authorization: Bearer {sanctum_token}
```

Le token est obtenu via `POST /api/v1/client/auth/login` ou `POST /api/v1/driver/auth/login`.

### Middlewares globaux API v1

| Middleware | Alias | Rôle |
|---|---|---|
| `ForceJsonResponse` | `api.json` | Force `Content-Type: application/json` |
| `SanitizeApiInput` | `api.sanitize` | Nettoyage des inputs |
| `ApiSecurityHeaders` | `api.security` | Headers de sécurité API |

### Endpoints Client (`/api/v1/client/`)

| Méthode | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/auth/register` | Non | Inscription client |
| `POST` | `/auth/login` | Non | Connexion client |
| `GET` | `/auth/me` | Oui | Profil courant |
| `POST` | `/auth/logout` | Oui | Déconnexion |
| `PATCH` | `/auth/profile` | Oui | Mise à jour profil |
| `PATCH` | `/auth/fcm-token` | Oui | Enregistrement token FCM |
| `DELETE` | `/auth/fcm-token` | Oui | Suppression token FCM |
| `GET` | `/addresses` | Oui | Liste adresses |
| `POST` | `/addresses` | Oui | Ajouter adresse |
| `PATCH` | `/addresses/{id}` | Oui | Modifier adresse |
| `DELETE` | `/addresses/{id}` | Oui | Supprimer adresse |
| `POST` | `/orders` | Oui | Créer commande |
| `GET` | `/orders/history` | Oui | Historique commandes |
| `POST` | `/orders/{id}/cancel` | Oui | Annuler commande |
| `GET` | `/orders/track/{token}` | Non | Suivi public par token |
| `POST` | `/payment/{orderId}/initiate` | Oui | Initier paiement |
| `GET` | `/payment/{orderId}/status` | Oui | Statut paiement |
| `GET` | `/payment/success` | Non | Callback succès Wave |
| `GET` | `/payment/error` | Non | Callback erreur Wave |

### Endpoints Livreur (`/api/v1/driver/`)

| Méthode | Endpoint | Auth | Description |
|---|---|---|---|
| `POST` | `/auth/register` | Non | Inscription livreur |
| `POST` | `/auth/login` | Non | Connexion livreur |
| `GET` | `/auth/me` | Oui | Profil livreur |
| `POST` | `/auth/logout` | Oui | Déconnexion |
| `PATCH` | `/auth/fcm-token` | Oui | Token FCM push |
| `POST` | `/status` | Oui | Changer statut disponibilité |
| `PATCH` | `/location` | Oui | Mise à jour position GPS (30 req/min) |
| `GET` | `/deliveries/pending` | Oui | Livraisons disponibles |
| `GET` | `/deliveries/active` | Oui | Livraison en cours |
| `POST` | `/deliveries/{id}/accept` | Oui | Accepter livraison |
| `POST` | `/deliveries/{id}/decline` | Oui | Refuser livraison |
| `PATCH` | `/deliveries/{id}/status` | Oui | Mettre à jour statut livraison |
| `GET` | `/earnings` | Oui | Résumé gains |
| `GET` | `/earnings/history` | Oui | Historique gains |
| `POST` | `/earnings/payout` | Oui | Demande de virement (max 3/jour) |

### Endpoints Restaurant (`/api/v1/restaurant/`)

| Méthode | Endpoint | Auth | Description |
|---|---|---|---|
| `GET` | `/delivery/orders` | Oui (restaurant) | Commandes en attente livraison |
| `POST` | `/delivery/orders/{id}/confirm` | Oui | Confirmer commande |
| `POST` | `/delivery/orders/{id}/ready` | Oui | Marquer prête |
| `GET` | `/delivery/settings` | Oui | Paramètres livraison |
| `PATCH` | `/delivery/settings` | Oui | Modifier paramètres |

### Endpoints Super Admin (`/api/v1/admin/`)

Tous protégés par `auth:sanctum` + `super.admin` + `throttle:api.admin`.

- **Livreurs** : liste, détail, approuver, rejeter, suspendre, réactiver
- **Restaurants plateforme** : liste, activer/désactiver, modifier commission
- **Analytics** : dashboard, livraisons live, commissions, gains livreurs

### Endpoints Publics

| Endpoint | Description |
|---|---|
| `GET /api/v1/restaurants` | Liste restaurants plateforme |
| `GET /api/v1/restaurants/nearby` | Restaurants proches (lat/lon) |
| `GET /api/v1/restaurants/{id}` | Détail restaurant |
| `GET /api/v1/restaurants/{id}/menu` | Menu complet |
| `GET /api/v1/restaurants/{id}/delivery-estimate` | Estimation livraison |
| `GET /api/v1/config` | Configuration publique plateforme |
| `GET /api/v1/geocoding/reverse` | Géocodage inversé (lat/lon → adresse) |
| `GET /api/v1/geocoding/search` | Recherche d'adresse |

### Format de réponse standard

```json
{
  "data": { ... },
  "message": "...",
  "status": 200
}
```

Erreurs :
```json
{
  "message": "Unauthenticated.",
  "status": 401
}
```

---

## 11. WebSocket & Temps Réel

### Configuration Reverb

Laravel Reverb est le serveur WebSocket natif. Il remplace Pusher sans coût externe.

**Démarrage :**
```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

**Reverse proxy recommandé (Nginx) :**
```nginx
location /app {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
}
```

### Configuration Echo.js (frontend)

```javascript
// resources/js/echo.js (ou bootstrap.js)
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT,
    wssPort: import.meta.env.VITE_REVERB_PORT,
    forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
    enabledTransports: ['ws', 'wss'],
});
```

### Canaux de broadcast

| Canal | Type | Événements |
|---|---|---|
| `restaurant.{id}.orders` | Public | `order.status_changed`, `order.created` |
| `delivery.{id}` | Public | `delivery.status_changed`, `driver.assigned` |
| `driver.{id}` | Public | `driver.location_updated`, `delivery.new` |
| `crm.leads` | Public | `lead.created`, `lead.status_changed` |
| `crm.commissions` | Public | `commission.credited`, `grade.changed` |
| `crm.withdrawals` | Public | `withdrawal.approved`, `withdrawal.paid`, `withdrawal.rejected` |

### Events broadcastés

| Event | Canal | Payload clé |
|---|---|---|
| `OrderStatusChanged` | `restaurant.{id}.orders` | `id`, `reference`, `old_status`, `new_status`, `customer_name` |
| `OrderCreated` | `restaurant.{id}.orders` | Données commande |
| `DeliveryStatusChanged` | `delivery.{id}` | Statut livraison |
| `DriverAssigned` | `delivery.{id}` | Informations livreur |
| `DriverLocationUpdated` | `driver.{id}` | `latitude`, `longitude`, `timestamp` |
| `NewDeliveryAvailable` | `driver.{id}` | Données commande disponible |
| `Crm\LeadCreated` | `crm.leads` | Données lead |
| `Crm\CommissionCredited` | `crm.commissions` | Montant, solde |
| `Crm\GradeChanged` | `crm.commissions` | Nouveau grade |

### Variables d'environnement frontend (Vite)

Toutes les variables préfixées `VITE_` sont exposées au frontend :
- `VITE_REVERB_APP_KEY`
- `VITE_REVERB_HOST`
- `VITE_REVERB_PORT`
- `VITE_REVERB_SCHEME`
- `VITE_APP_NAME`

---

## 12. Déploiement Production

### Prérequis serveur

- PHP 8.2+ avec extensions : `pdo_mysql`, `openssl`, `mbstring`, `xml`, `gd`, `zip`, `bcmath`, `pcntl` (pour les queues)
- MySQL 8.0+ avec `utf8mb4_unicode_ci`
- Composer 2.x (installé sur le serveur)
- Node.js + npm (pour la build, peut être fait localement puis uploadé)
- Accès SSH ou terminal cPanel

### Script de déploiement (`deploy.sh`)

Fichier : `deploy.sh` (à la racine, exécuter depuis `~/`)

```bash
bash ~/deploy.sh
```

Le script effectue dans l'ordre :
1. `git stash` + `git pull origin main` + `git stash pop`
2. `composer install --no-dev --no-interaction --optimize-autoloader`
3. Copie des assets publics (`build/`, `images/`, `sounds/`, PWA, Firebase SW)
4. `php artisan migrate --force`
5. Nettoyage cache : `view:clear`, `cache:clear`, `route:cache`, `config:clear`

### Checklist complète (production)

```bash
# 1. Variables d'environnement
APP_ENV=production
APP_DEBUG=false
APP_KEY=<générer avec key:generate>

# 2. Assets (build local, upload)
npm run build
# Copier public/build/ sur le serveur

# 3. Optimisation Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 4. Migrations
php artisan migrate --force

# 5. Permissions fichiers
chmod -R 755 storage/ bootstrap/cache/
chown -R www-data:www-data storage/ bootstrap/cache/

# 6. Queue worker (via Supervisor ou crontab cPanel)
php artisan queue:work --sleep=3 --tries=3 --timeout=90

# 7. Scheduler (crontab)
* * * * * cd /chemin/vers/MenuPro && php artisan schedule:run >> /dev/null 2>&1

# 8. Reverb WebSocket (via Supervisor)
php artisan reverb:start --host=0.0.0.0 --port=8080
```

### Configuration Supervisor (queue worker)

```ini
[program:menupro-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /home/user/MenuPro/artisan queue:work --sleep=3 --tries=3 --timeout=90
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
stdout_logfile=/var/log/menupro-worker.log
```

### Configuration Supervisor (Reverb)

```ini
[program:menupro-reverb]
process_name=%(program_name)s
command=php /home/user/MenuPro/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
user=www-data
stdout_logfile=/var/log/menupro-reverb.log
```

### Checklist sécurité post-déploiement

- [ ] `APP_DEBUG=false`
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] HTTPS activé, certificat SSL valide
- [ ] `BCRYPT_ROUNDS=12`
- [ ] `WAVE_SIGNING_SECRET` et `LYGOS_WEBHOOK_SECRET` configurés
- [ ] `storage/` non accessible publiquement
- [ ] `.env` non accessible publiquement (`.htaccess` ou config Nginx)
- [ ] Firebase Service Account JSON stocké dans `system_settings` (pas dans `.env`)

---

## 13. Commandes Artisan Utiles

### Setup & Migrations

```bash
# Exécuter toutes les migrations
php artisan migrate

# Exécuter les migrations en production (sans confirmation)
php artisan migrate --force

# Rollback la dernière migration
php artisan migrate:rollback

# Voir le statut des migrations
php artisan migrate:status

# Rafraîchir la base (ATTENTION : détruit toutes les données)
php artisan migrate:fresh --seed
```

### Queue Workers

```bash
# Démarrer le worker (développement, 1 seule tentative)
php artisan queue:listen --tries=1

# Démarrer le worker (production)
php artisan queue:work --sleep=3 --tries=3 --timeout=90

# Voir les jobs en attente
php artisan queue:monitor

# Vider les jobs en échec
php artisan queue:flush

# Retenter les jobs en échec
php artisan queue:retry all
```

### Reverb WebSocket

```bash
# Démarrer le serveur Reverb
php artisan reverb:start

# Démarrer avec configuration explicite
php artisan reverb:start --host=0.0.0.0 --port=8080

# Redémarrer Reverb (après deploy)
php artisan reverb:restart
```

### Cache & Optimisation

```bash
# Vider tous les caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Reconstruire les caches (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimisation complète production
php artisan optimize
```

### Scheduler

```bash
# Lancer les tâches planifiées (à appeler chaque minute via cron)
php artisan schedule:run

# Voir les tâches planifiées
php artisan schedule:list
```

### Tinker (console interactive)

```bash
php artisan tinker

# Exemples dans Tinker :
# Créer un Super Admin
$user = \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@menupro.ci', 'password' => bcrypt('secret')]);
$user->role = \App\Enums\UserRole::SUPER_ADMIN;
$user->email_verified_at = now();
$user->save();

# Vérifier un SystemSetting
\App\Models\SystemSetting::get('firebase_project_id');

# Invalider le cache d'un setting
\Illuminate\Support\Facades\Cache::forget('system_setting_firebase_project_id');

# Tester FCM
app(\App\Services\FcmService::class)->isConfigured();
```

### Logs

```bash
# Viewer de logs en temps réel (développement)
php artisan pail --timeout=0

# Vider les logs
php artisan log:clear  # (si disponible) ou : > storage/logs/laravel.log
```

---

## Annexe : Structure des fichiers clés

```
app/
├── Enums/
│   ├── UserRole.php          — 9 rôles (super_admin → delivery_driver)
│   ├── OrderStatus.php       — 10 statuts commandes avec transitions
│   ├── RestaurantStatus.php  — pending/active/suspended/rejected
│   └── Crm/
│       ├── LeadStatus.php    — 8 statuts pipeline CRM
│       └── ...
├── Events/                   — Events WebSocket (broadcast)
├── Http/
│   ├── Controllers/
│   │   ├── Auth/             — Login, Register, PasswordReset, EmailVerification
│   │   ├── Public/           — Menu, Checkout, OrderStatus, Geocoding
│   │   ├── Restaurant/       — Dashboard, Orders, KDS, QRCode, Settings...
│   │   ├── SuperAdmin/       — Restaurants, Plans, Users, Stats...
│   │   ├── Commando/         — AgentDashboard, Verification, Welcome
│   │   ├── Api/V1/           — Client, Driver, Restaurant, Admin APIs
│   │   └── Webhook/          — Wave, MoneyFusion webhooks
│   └── Middleware/           — 16 middlewares (sécurité, rôles, scope)
├── Livewire/                 — Composants Livewire (Restaurant, Public, Commando)
├── Models/
│   ├── User.php              — Multi-rôle, multi-tenant
│   ├── Restaurant.php        — Entité centrale, SoftDeletes
│   ├── Order.php / OrderItem.php
│   ├── SystemSetting.php     — Clé/valeur avec cache 5 min
│   ├── DeliveryDriver.php    — Livreurs plateforme
│   └── Crm/                  — Team, Lead, Commission, Wallet...
└── Services/
    ├── FcmService.php        — FCM HTTP v1 avec JWT RS256
    └── ...

routes/
├── web.php    — Routes publiques, auth, restaurant admin, super admin, KDS
├── api.php    — API v1 (client, driver, restaurant, admin)
└── channels.php

database/migrations/     — ~109 migrations (2024-01 → 2026-07)
deploy.sh                — Script déploiement cPanel
.env.example             — Template complet des variables d'environnement
```


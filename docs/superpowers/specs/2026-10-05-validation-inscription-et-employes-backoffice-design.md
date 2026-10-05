# Validation des inscriptions & Employés back-office — Spec de conception

**Date :** 2026-10-05
**Statut :** À relire par le porteur du projet avant planification

## Intention (ce que veut le porteur)

1. **Valider une inscription avant tout accès.** Aujourd'hui, toute personne qui remplit le
   formulaire d'inscription restaurant obtient immédiatement un compte actif + un essai de 7 jours
   + l'accès complet au dashboard. Le porteur a constaté que des personnes s'inscrivent **uniquement
   pour inspecter le dashboard et reproduire le concept**. Il faut que les écrans internes ne soient
   jamais visibles sans une approbation humaine.
2. **Déléguer sans tout ouvrir.** Le porteur veut créer des comptes **employés back-office** qui ne
   gèrent que **certaines sections** de l'admin (ex. un employé « modération » qui valide les
   inscriptions), sans être des super admins complets.
3. **Contrainte forte : budget limité.** Aucune solution payante (pas de SMS/OTP, pas d'email payant,
   pas de librairie externe). On réutilise au maximum l'existant.

## Contexte du code existant (vérifié)

- `RestaurantStatus` possède déjà `PENDING | ACTIVE | SUSPENDED | EXPIRED`.
- Le workflow de modération **existe déjà** : `SuperAdmin\RestaurantController::approve()` (ligne 150),
  `reject()`, `suspend()`, `reactivate()`. `approve()` appelle `Restaurant::validate()` (modèle
  ligne 396), active l'abonnement en attente et **notifie le propriétaire**
  (`RestaurantValidatedNotification`). `reject()` exige un motif et notifie.
- La sidebar admin affiche déjà un compteur « restaurants en attente » (`$pendingRestaurants`).
- `EnsureRestaurantActive` **autorise déjà** un resto `PENDING` à accéder au dashboard et bloque sa
  page publique — c'est exactement l'inverse de ce qu'on veut pour le dashboard.
- **Le problème** : `RegisterController::store()` (ligne ~156) crée le restaurant en
  `status = ACTIVE`, met `email_verified_at = now()`, puis `auth()->login()` + redirige vers le
  dashboard. Toute la machinerie d'approbation est donc du code mort pour les auto-inscriptions.
- Back-office : routes groupées sous `Route::prefix('admin')->name('super-admin.')
  ->middleware(['auth','super.admin'])` (web.php ligne 363). `EnsureSuperAdmin` est binaire.
  `Gate::before` (AppServiceProvider ligne 92) accorde **tout** à tout `SUPER_ADMIN`.
  `SuperAdmin\UserController::store()` ne sait créer qu'un `role = super_admin`.
  Aucun système de permissions (ni Spatie, ni table permissions).

## Décisions validées (dialogue de cadrage)

- Inscription : **blocage total du dashboard jusqu'à validation** (protège les écrans des copieurs).
- **Pas d'OTP ni de vérification email** (coût). L'approbation humaine est le seul gate.
- Employés : **accès par section, cases à cocher** (pas de rôles prédéfinis, pas de lib).
- **Un seul niveau d'action** : une section cochée = voir **et** gérer (pas de « lecture seule »).

---

## Partie A — Validation des inscriptions restaurant

### A1. Inscription (`RegisterController::store`)
- Créer le restaurant en `status = RestaurantStatus::PENDING`.
- L'abonnement d'essai est créé mais **le compteur ne démarre pas** à l'inscription : `starts_at` et
  `ends_at` restent nuls (ou non significatifs) tant que l'admin n'a pas approuvé. Objectif : le resto
  ne perd pas de jours d'essai pendant l'attente de validation.
- Après enregistrement, l'utilisateur est connecté puis **redirigé vers un écran d'attente**
  (`restaurant.pending`) au lieu du dashboard.

### A2. Écran d'attente
- Nouvelle vue simple « Inscription reçue — en attente de validation » (statut + message, contact
  support). Aucune donnée interne sensible.

### A3. Blocage du dashboard (`EnsureRestaurantActive`)
- Pour un resto `PENDING` sur une route dashboard (`restaurant.*`) : rediriger vers `restaurant.pending`
  au lieu d'autoriser l'accès. Conserver le comportement `SUSPENDED` (redirige accueil) et `EXPIRED`
  (page abonnement). Les super admins continuent de bypasser.

### A4. Approbation (ajustement de l'existant)
- `approve()` : en plus d'activer le resto, **démarrer le compteur d'essai** si l'abonnement trouvé
  est l'essai (poser `starts_at = now()`, `ends_at = now() + trial_days`, et `subscription_ends_at`
  du restaurant). Conserver le chemin existant pour les abonnements payants `PENDING`.
- `reject()` : inchangé (motif obligatoire + notification).

### A5. Hors scope A (YAGNI)
- Pas d'auto-approbation (ex. restos amenés par un agent Commando) — envisageable plus tard si le
  volume de validation manuelle devient trop lourd.

---

## Partie B — Employés back-office avec accès par section

### B1. Modèle de données
- Migration : colonne `admin_permissions` **JSON nullable** sur `users`.
  - `null` → **admin complet** (le porteur et assimilés) : accès total.
  - tableau de clés de sections (ex. `["restaurants","orders"]`) → **employé restreint**.
- Cast `array` sur le modèle `User`.

### B2. Clés de sections
**Délégables** (proposées à cocher) :
`restaurants`, `orders`, `deliveries`, `subscriptions`, `crm`, `announcements`, `customers`, `finance`.

**Réservées aux admins complets, jamais délégables** (ne figurent pas dans les cases) :
gestion des **employés/utilisateurs** (`super-admin.utilisateurs*`), **paramètres système**,
**config paiement / Jeko KYC** (`super-admin.payment-settings*`, `super-admin.jeko*` — contiennent des
secrets). → Un employé ne peut ni créer d'autres employés ni s'auto-accorder des droits.

> Le mappage exact clé → préfixes de noms de routes (`super-admin.restaurants*`, etc.) sera figé dans
> le plan d'implémentation.

### B3. Contrôle d'accès
- `User::canAdminSection(string $section): bool` → vrai si admin complet (`admin_permissions === null`)
  **ou** si `$section` est dans la liste.
- `User::isFullAdmin(): bool` → `isSuperAdmin() && admin_permissions === null`.
- `Gate::before` (AppServiceProvider) : le bypass « tout autorisé » ne s'applique **qu'aux admins
  complets**. Les employés restreints ne bypassent plus et passent par les vérifs de section.
- Nouveau middleware `EnsureAdminSection` (alias `admin.section:<clé>`) : exige `isSuperAdmin()` **et**
  `canAdminSection(<clé>)`, sinon `403`. Posé sur chaque sous-groupe de routes admin, en plus de
  `super.admin`.
- Sidebar (`components.layouts.admin-super`) : chaque lien de section n'est rendu que si
  `canAdminSection(...)`. Les sections réservées ne s'affichent que pour les admins complets.
- Redirection post-login : un employé restreint est envoyé vers sa **première section autorisée**
  (pas le dashboard global, qui agrège des stats finance). À définir : route de repli si aucune section.

### B4. Création / gestion d'employés (`SuperAdmin\UserController`)
- `store()` : étendre pour créer un employé — nom, email, téléphone, mot de passe + liste de sections
  cochées validée contre la liste des clés délégables. Rôle = `SUPER_ADMIN` avec `admin_permissions`
  = tableau de sections. Un admin complet se distingue par `admin_permissions = null`.
- Accès à la section « Utilisateurs/Employés » **réservé aux admins complets** (`admin.section` ne
  couvre pas cette section ; garde explicite `isFullAdmin()`).
- UI : formulaire de création avec cases à cocher ; la liste des utilisateurs affiche le périmètre
  (sections) de chaque employé.

### B5. Correctif sécurité lié (cheap)
- Bloquer le login si `users.is_active === false` (dans `LoginRequest::authenticate` / le flux de
  login). Aujourd'hui un compte désactivé peut potentiellement se reconnecter. Désactiver un employé
  doit réellement lui couper l'accès.

### B6. Hors scope B (YAGNI)
- Pas de niveau « lecture seule », pas de rôles prédéfinis, pas de permissions au niveau ligne
  (un employé « restaurants » voit tous les restaurants), pas de journal d'audit dédié au-delà de
  l'`ActivityLog` existant.

---

## Tests (automatisés, gratuits)

**Partie A**
- Inscription → restaurant `PENDING`, essai non démarré, redirection vers `restaurant.pending`.
- Resto `PENDING` tentant une route dashboard → redirigé vers l'écran d'attente (pas d'accès).
- `approve()` → resto `ACTIVE`, compteur d'essai démarré (`ends_at` ≈ now + trial_days).

**Partie B**
- Admin complet (`admin_permissions = null`) → accès à toutes les sections.
- Employé `["restaurants"]` → accède à `super-admin.restaurants*`, reçoit **403** sur `finance` et sur
  la gestion des employés.
- Un employé ne peut pas atteindre la création d'employés ni modifier ses propres permissions.
- Login refusé quand `is_active = false`.

## Points à confirmer au moment du plan
- Liste finale des clés de sections et leur mappage vers les noms de routes réels (certains
  contrôleurs — Finance, Transactions, Payouts — doivent être regroupés sous la clé `finance`).
- Route de repli pour un employé sans aucune section autorisée.
- Faut-il conserver le comportement « page publique bloquée pour PENDING » tel quel (oui, inchangé).

## Impact / risques
- `Gate::before` et le groupe de routes `super.admin` sont des points centraux : toute erreur ouvre ou
  ferme trop. Les tests d'autorisation (B) sont la garde-fou principale.
- Changer l'inscription en `PENDING` modifie le parcours d'onboarding : bien vérifier qu'aucun autre
  flux (agents Commando, CRM, seeders) ne suppose un resto immédiatement actif.

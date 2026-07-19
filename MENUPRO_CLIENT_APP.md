# MenuPro — Référence complète : Application Client

> Document de référence pour le développement de l'application mobile client.
> Domaine : `menupro.ci` | Dernière mise à jour : 2026-07-17

---

## Table des matières

1. [Architecture générale](#1-architecture-générale)
2. [Types d'établissements](#2-types-détablissements)
3. [Authentification](#3-authentification)
4. [Endpoints API — Client](#4-endpoints-api--client)
5. [Endpoints API — Restaurants & Menu](#5-endpoints-api--restaurants--menu)
6. [Endpoints API — Commandes](#6-endpoints-api--commandes)
7. [Endpoints API — Paiement](#7-endpoints-api--paiement)
8. [Endpoints API — Géocodage](#8-endpoints-api--géocodage)
9. [Endpoints API — Ticker (annonces bannière)](#9-endpoints-api--ticker-annonces-bannière)
10. [Endpoints API — Bannières promotionnelles](#10-endpoints-api--bannières-promotionnelles)
11. [Routes web publiques](#11-routes-web-publiques)
12. [Flux complet de commande](#12-flux-complet-de-commande)
13. [Formats de réponse détaillés](#13-formats-de-réponse-détaillés)
14. [Structure des options de plats](#14-structure-des-options-de-plats)
15. [Horaires d'ouverture](#15-horaires-douverture)
16. [Paiement — Wave & Paiement à la livraison](#16-paiement--wave--paiement-à-la-livraison)
17. [Notifications push FCM](#17-notifications-push-fcm)
18. [Suivi de commande temps réel](#18-suivi-de-commande-temps-réel)
19. [Calcul des frais de livraison](#19-calcul-des-frais-de-livraison)
20. [Statuts & enums](#20-statuts--enums)
21. [Modèle de données](#21-modèle-de-données)
22. [Rate limits](#22-rate-limits)
23. [Gestion des erreurs](#23-gestion-des-erreurs)
24. [Notes importantes](#24-notes-importantes)

---

## 1. Architecture générale

```
Base URL         : https://menupro.ci/api/v1
Format           : JSON exclusivement
Auth             : Bearer token (Laravel Sanctum)
Monnaie          : FCFA — tous les montants en entiers (integer)
                   Exemple : 1 250 F CFA = 125000 dans l'API
GPS              : WGS84 (latitude/longitude décimaux)
Timezone         : Africa/Abidjan (UTC+0)
```

### Middleware globaux (toutes les routes `/api/v1/`)

| Middleware | Alias | Rôle |
|-----------|-------|------|
| `ForceJsonResponse` | `api.json` | Toujours du JSON en retour, quel que soit l'en-tête `Accept` |
| `SanitizeApiInput` | `api.sanitize` | `trim()` + `strip_tags()` sur tous les inputs (sauf `password`, `fcm_token`, `token`, `secret`) |
| `ApiSecurityHeaders` | `api.security` | Headers HTTP de sécurité |

---

## 2. Types d'établissements

MenuPro accueille plusieurs catégories d'établissements. Chaque établissement a un `type` (champ `restaurant.type`) qui détermine son plan tarifaire et les fonctionnalités disponibles.

### 2.1 Établissements complets (plan Standard / Pro / Ultime)

Ces établissements ont accès à toutes les fonctionnalités : menu, commandes, livraison, réservations, caisse POS, statistiques, équipe, stocks, etc.

| `type` | Label affiché |
|--------|--------------|
| `restaurant` | Restaurant |
| `bar` | Bar |
| `brasserie` | Brasserie |
| `maquis` | Maquis |
| `traiteur` | Traiteur |
| `cafe` | Café |
| `food_truck` | Food Truck |
| `brunch` | Brunch |
| `evenementiel` | Événementiel |

---

### 2.2 Micro-commerces (plan Stand — 5 000 FCFA/mois)

Ces établissements ont une interface **simplifiée** — uniquement les fonctionnalités essentielles.

| `type` | Label affiché |
|--------|--------------|
| `stand` | Stand |
| `kiosque` | Kiosque |
| `cantine` | Cantine |
| `patisserie` | Pâtisserie |
| `epicerie` | Épicerie |
| `jus` | Bar à jus |
| `snack` | Snack |

**Fonctionnalités disponibles pour les Stands :**
- Menu (plats & catégories)
- Commandes en ligne (QR code sur place)
- Paiements
- Apparence du site (couleurs)
- Paramètres de base

**Fonctionnalités NON disponibles pour les Stands :**
- Livraison (`feature:delivery` requis)
- Réservations de tables
- Caisse POS
- Statistiques avancées
- Gestion d'équipe
- Stock avancé
- Cuisine (vue cuisine)

---

### 2.3 Identifier le type dans l'app

Chaque objet restaurant retourné par l'API inclut :
```json
{
  "type": "maquis",
  "platform_category": "ivoirien",
  "is_on_platform": true
}
```

- `type` → type d'établissement (voir tableaux ci-dessus)
- `platform_category` → catégorie de recherche dans l'app (ex: `ivoirien`, `pizza`, `fastfood`, `burger`, `cafe`, `patisserie`)
- `is_on_platform` → `true` si l'établissement est visible dans l'app livraison plateforme

Pour afficher le bon **libellé et icône** dans l'app, se baser sur le `type`. Les micro-commerces (plan Stand) ne proposent pas la livraison — vérifier aussi `delivery_enabled` ou `is_on_platform`.

---

## 3. Authentification



### 2.1 Inscription

```
POST /api/v1/client/auth/register
Rate limit : 5 requêtes/heure par IP
```

**Corps :**

| Champ | Type | Règles |
|-------|------|--------|
| `name` | string | required, max:100 |
| `phone` | string | required, max:20, **unique** |
| `email` | string | nullable, email, max:150, unique |
| `password` | string | required, min:6 |
| `city` | string | nullable, max:100 |

**Réponse 201 :**
```json
{
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "customer": {
    "id": 1,
    "name": "Jean Dupont",
    "email": "jean@example.com",
    "phone": "+2250701234567",
    "city": "Abidjan",
    "total_orders": 0
  }
}
```

---

### 2.2 Connexion

```
POST /api/v1/client/auth/login
Rate limit : 5/min par IP + 5/15min par compte
```

**Corps :**
```json
{
  "phone": "0701234567",
  "password": "monmotdepasse"
}
```

**Réponse 200 :** même structure que l'inscription.

**Erreurs :**
| Code | Message |
|------|---------|
| 404 | `"Compte introuvable."` |
| 401 | `"Mot de passe incorrect."` |
| 403 | `"Compte suspendu. Contactez le support."` |

Note : les anciens tokens `customer-app` sont révoqués automatiquement à chaque connexion.

---

### 2.3 Utilisation du token

Toutes les routes **[Auth]** nécessitent :
```
Authorization: Bearer {token}
```

---

### 2.4 Profil

```
GET   /api/v1/client/auth/me         [Auth]  → objet customer
POST  /api/v1/client/auth/logout     [Auth]  → révoque le token courant
PATCH /api/v1/client/auth/profile    [Auth]  → modifier nom, ville, email
```

**Corps PATCH profile** (champs `sometimes`) :
```json
{
  "name": "Jean Dupont",
  "city": "Abidjan",
  "email": "jean@example.com"
}
```

---

### 2.5 Token FCM (notifications push)

```
PATCH  /api/v1/client/auth/fcm-token   [Auth]   Enregistrer
DELETE /api/v1/client/auth/fcm-token   [Auth]   Supprimer (à la déconnexion)
```

**Corps PATCH :**
```json
{ "fcm_token": "fXxXxXxXxXxX..." }
```

---

## 4. Endpoints API — Client

### 3.1 Adresses sauvegardées

```
GET    /api/v1/client/addresses          [Auth]  Liste
POST   /api/v1/client/addresses          [Auth]  Créer
PATCH  /api/v1/client/addresses/{id}     [Auth]  Modifier
DELETE /api/v1/client/addresses/{id}     [Auth]  Supprimer
```

**Corps POST/PATCH :**

| Champ | Type | Règles |
|-------|------|--------|
| `label` | string | required (POST), max:50 — Ex: "Maison", "Bureau" |
| `address` | string | required (POST), max:300 |
| `city` | string | required (POST), max:100 |
| `zone` | string | nullable, max:100 (quartier) |
| `latitude` | numeric | nullable |
| `longitude` | numeric | nullable |
| `instructions` | string | nullable, max:300 |
| `is_default` | boolean | nullable |

Si `is_default=true` → toutes les autres adresses du client sont désactivées (atomique).

**Réponse GET :**
```json
{
  "data": [
    {
      "id": 1,
      "label": "Maison",
      "address": "Rue des Fleurs, Cocody",
      "city": "Abidjan",
      "zone": "Cocody",
      "latitude": "5.3592800",
      "longitude": "-4.0082300",
      "instructions": "2ème portail bleu",
      "is_default": true
    }
  ]
}
```

---

## 5. Endpoints API — Restaurants & Menu

> Routes publiques — sans authentification — Rate limit : 120/min par IP

### 4.0 Catégories plateforme

```
GET /api/v1/restaurants/categories
```

Retourne les catégories actives sur la plateforme avec le nombre d'établissements, triées par popularité décroissante. Utile pour peupler dynamiquement le sélecteur de catégories dans l'app.

**Réponse :**
```json
{
  "data": [
    { "key": "restaurant",  "label": "Restaurant",              "count": 24 },
    { "key": "ivoirien",    "label": "Cuisine ivoirienne",      "count": 8  },
    { "key": "stand",       "label": "Stand / Kiosque",         "count": 5  },
    { "key": "maquis",      "label": "Maquis / Bar",            "count": 4  },
    { "key": "patisserie",  "label": "Pâtisserie / Boulangerie","count": 2  }
  ]
}
```

**Champs :**

| Champ | Type | Notes |
|-------|------|-------|
| `key` | string | Valeur à passer en `?category=` sur les autres endpoints |
| `label` | string | Libellé traduit pour affichage |
| `count` | integer | Nombre d'établissements actifs dans cette catégorie |

Seules les catégories ayant au moins 1 établissement `active` + `is_on_platform=true` sont retournées.

---

### 4.1 Liste des restaurants

```
GET /api/v1/restaurants
```

**Paramètres query :**

| Param | Type | Description |
|-------|------|-------------|
| `city` | string | Filtrer par ville |
| `category` | string | Filtrer par catégorie plateforme |
| `lat` | numeric | Latitude client (active le tri par distance) |
| `lng` | numeric | Longitude client |
| `open_now` | boolean | `true` pour afficher uniquement les ouverts |

**Réponse :**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Chez Tante Marie",
      "slug": "chez-tante-marie",
      "category": "ivoirien",
      "city": "Abidjan",
      "address": "Rue des Bananiers, Cocody",
      "phone": "+2250701234567",
      "logo_url": "https://menupro.ci/storage/logos/xxx.jpg",
      "banner_url": "https://menupro.ci/storage/banners/xxx.jpg",
      "is_open": true,
      "min_order_amount": 200000,
      "avg_prep_time": 25,
      "latitude": 5.3592,
      "longitude": -4.0082,
      "distance_km": 1.3
    }
  ]
}
```

`distance_km` est présent uniquement si `lat` et `lng` sont fournis.

---

### 4.2 Restaurants proches

```
GET /api/v1/restaurants/nearby?lat=5.3534&lng=-4.0012&radius_km=5
```

Même format que la liste + par restaurant :
```json
{
  "distance_km": 2.3,
  "delivery_fee": 85000,
  "estimated_minutes": 40,
  "within_range": true
}
```

---

### 4.3 Détail restaurant

```
GET /api/v1/restaurants/{id}
```

Mêmes champs que la liste + champs supplémentaires :
```json
{
  "description": "Cuisine traditionnelle ivoirienne...",
  "tagline": "Le goût d'ici",
  "opening_hours": {
    "monday": { "is_open": true, "open": "08:00", "close": "22:00" },
    "tuesday": { "is_open": true, "open": "08:00", "close": "22:00" },
    "wednesday": { "is_open": true, "open": "08:00", "close": "22:00" },
    "thursday": { "is_open": true, "open": "08:00", "close": "22:00" },
    "friday": { "is_open": true, "open": "08:00", "close": "23:00" },
    "saturday": { "is_open": true, "open": "10:00", "close": "23:00" },
    "sunday": { "is_open": false, "open": "00:00", "close": "00:00" }
  },
  "delivery_base_fee": 50000,
  "delivery_fee_per_km": 15000,
  "max_delivery_km": 10,
  "delivery_city_name": "Abidjan"
}
```

---

### 4.4 Menu complet

```
GET /api/v1/restaurants/{id}/menu
```

**Réponse :**
```json
{
  "restaurant_id": 1,
  "currency": "XOF",
  "categories": [
    {
      "id": 3,
      "name": "Plats principaux",
      "dishes": [
        {
          "id": 12,
          "name": "Attiéké Poisson",
          "description": "Semoule de manioc avec poisson braisé...",
          "price": 125000,
          "compare_price": 150000,
          "image_url": "https://menupro.ci/storage/dishes/xxx.jpg",
          "is_available": true,
          "is_featured": false,
          "is_spicy": false,
          "is_vegetarian": false,
          "prep_time": 15,
          "calories": null
        }
      ]
    }
  ]
}
```

Note : les groupes d'options (extras, tailles) ne sont pas inclus dans cette réponse. Voir §12 pour la structure des options.

---

### 4.5 Estimation frais de livraison

```
GET /api/v1/restaurants/{id}/delivery-estimate?lat=5.3612&lng=-3.9814
```

**Réponse succès :**
```json
{
  "deliverable": true,
  "delivery_fee": 75000,
  "distance_km": 3.2,
  "estimated_minutes": 45,
  "is_peak_hour": false,
  "breakdown": {
    "base_fee": 50000,
    "distance_fee": 25000,
    "peak_surcharge": 0,
    "prep_minutes": 20,
    "transit_minutes": 25
  }
}
```

**Réponse si hors zone :**
```json
{
  "deliverable": false,
  "message": "Ce restaurant ne livre pas à cette adresse.",
  "distance_km": 18.5,
  "city_covered": false
}
```

---

## 6. Endpoints API — Commandes

### 5.1 Créer une commande

```
POST /api/v1/client/orders     [Auth]
Rate limit : 20/heure par client
```

**Corps :**

| Champ | Type | Règles |
|-------|------|--------|
| `restaurant_id` | integer | required, exists |
| `items` | array | required, min:1 |
| `items[].dish_id` | integer | required, exists |
| `items[].quantity` | integer | required, min:1, max:20 |
| `items[].notes` | string | nullable, max:200 |
| `delivery_lat` | numeric | required, between:-90,90 |
| `delivery_lng` | numeric | required, between:-180,180 |
| `delivery_address` | string | required, max:300 |
| `delivery_city` | string | required, max:100 |
| `delivery_instructions` | string | nullable, max:300 |
| `customer_notes` | string | nullable, max:300 |
| `payment_method` | string | required, `in:wave,orange_money,mtn_money,cash_on_delivery` |

Validations supplémentaires :
- Restaurant doit avoir `is_on_platform=true` et `status=active`
- Adresse doit être dans la zone de livraison (`within_range=true`)
- `subtotal >= restaurant.min_order_amount`

**Calcul automatique :**
```
subtotal          = somme(dish.price × quantity)
delivery_fee      = DeliveryPricingService (base + distance + peak)
platform_comm.    = round(subtotal × commission_rate / 100)  ← défaut 12%
total             = subtotal + delivery_fee
```

**Réponse 201 :**
```json
{
  "order": { /* OrderObject — voir §11.1 */ },
  "tracking_token": "a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4",
  "next_step": "payment",
  "payment_url": "https://menupro.ci/api/v1/client/payment/42/initiate"
}
```

---

### 5.2 Historique des commandes

```
GET /api/v1/client/orders/history     [Auth]
```

15 commandes par page, triées par date décroissante.

```json
{
  "data": [ /* tableau d'OrderObject */ ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "total": 42
  }
}
```

---

### 5.3 Annuler une commande

```
POST /api/v1/client/orders/{id}/cancel     [Auth]
```

Annulation possible si `status` ∈ `[draft, pending_payment, paid, confirmed]`.

**Réponse 200 :** `{ "message": "Commande annulée." }`

**Réponse 422 :** `{ "message": "Cette commande ne peut plus être annulée." }`

---

### 5.4 Suivi d'une commande (sans auth)

```
GET /api/v1/client/orders/track/{tracking_token}
Rate limit : 120/min par IP
```

```json
{
  "order_status": "preparing",
  "order_status_label": "En préparation",
  "estimated_minutes": 45,
  "delivery": {
    "status": "heading_to_restaurant",
    "status_label": "En route vers le restaurant",
    "driver": {
      "name": "Kouamé B.",
      "phone": "+2250700000000",
      "latitude": 5.3500,
      "longitude": -4.0000,
      "vehicle": "moto",
      "rating": 4.8
    }
  },
  "timeline": {
    "ordered_at": "2026-07-17T10:30:00Z",
    "confirmed_at": "2026-07-17T10:32:00Z",
    "preparing_at": "2026-07-17T10:33:00Z",
    "ready_at": null,
    "driver_assigned_at": null,
    "picked_up_at": null,
    "completed_at": null
  }
}
```

`delivery` est `null` si le type de commande n'est pas `delivery` ou si aucun livreur n'est encore assigné.

---

## 7. Endpoints API — Paiement (initiation Wave)

### 6.1 Initier le paiement Wave

```
POST /api/v1/client/payment/{orderId}/initiate     [Auth]
Rate limit : 5/5min par client
```

Seul `wave` est opérationnel. Les autres méthodes retournent 422.

**Réponse 200 :**
```json
{
  "payment_url": "https://pay.wave.com/checkout/cs_xxxxx",
  "session_id": "cs_xxxxx",
  "order_id": 42,
  "amount": 325000,
  "tracking_token": "a1b2c3d4..."
}
```

Ouvrir `payment_url` dans un WebView ou le navigateur système.

---

### 6.2 Vérifier le statut du paiement

```
GET /api/v1/client/payment/{orderId}/status     [Auth]
```

```json
{
  "order_id": 42,
  "payment_status": "completed",
  "order_status": "paid",
  "paid_at": "2026-07-17T10:35:00Z"
}
```

---

### 6.3 Callbacks Wave (redirection après paiement)

```
GET /api/v1/client/payment/success?token={tracking_token}
GET /api/v1/client/payment/error?token={tracking_token}
```

Ces endpoints ne modifient pas l'état — c'est le webhook `/webhooks/wave` qui confirme définitivement.
Utiliser uniquement pour afficher un message intermédiaire.

**Réponse success :**
```json
{
  "message": "Redirection paiement reçue. Statut en cours de vérification.",
  "tracking_token": "...",
  "order_status": "paid",
  "payment_status": "completed"
}
```

**Réponse error (HTTP 400) :**
```json
{
  "message": "Le paiement a échoué ou été annulé. Veuillez réessayer.",
  "tracking_token": "...",
  "order_status": "pending_payment",
  "payment_status": "pending"
}
```

---

### 6.4 Flux de paiement complet

```
1. POST /api/v1/client/orders
   → { order_id: 42, tracking_token: "...", payment_url: "/payment/42/initiate" }

2. POST /api/v1/client/payment/42/initiate
   → { payment_url: "https://pay.wave.com/checkout/cs_xxxxx" }

3. Ouvrir l'URL Wave dans WebView / navigateur

4. Wave redirige vers:
   success → GET /api/v1/client/payment/success?token={tracking_token}
   echec   → GET /api/v1/client/payment/error?token={tracking_token}

5. Webhook Wave (asynchrone, côté serveur)
   → Confirme définitivement le paiement
   → order.status = "paid"
   → Notification push envoyée au client

6. Polling de vérification:
   GET /api/v1/client/payment/42/status
   → { payment_status: "completed" }
```

Ne jamais se fier uniquement au callback (étape 4) — toujours confirmer via polling (étape 6).

---

## 8. Endpoints API — Géocodage

> Publics, sans authentification.

### 7.1 Reverse geocoding (coords → adresse)

```
GET /api/v1/v1/geocoding/reverse?lat=5.3534&lng=-4.0012
```

Retourne l'adresse lisible (via Nominatim OSM, mise en cache 24h).

### 7.2 Recherche d'adresse (autocomplete)

```
GET /api/v1/v1/geocoding/search?q=Cocody+Angr%C3%A9&city=Abidjan
```

| Param | Règles |
|-------|--------|
| `q` | required, min:3, max:200 |
| `city` | nullable, défaut `"Abidjan"` |

Résultats mis en cache 1h. Max 5 résultats.

```json
{
  "data": [
    {
      "display_name": "Angré, Cocody, Abidjan, CI",
      "lat": 5.3592,
      "lng": -4.0082,
      "address": { "road": "...", "neighbourhood": "Angré", "city": "Abidjan" }
    }
  ]
}
```

---

## 9. Endpoints API — Ticker (annonces bannière)

Le ticker est un **bandeau défilant** affiché en haut de l'application mobile. Son contenu est géré par le Super Admin depuis le back-office.

### `GET /api/v1/ticker` — Public
Rate limit : 120/min par IP

Retourne les annonces actives marquées "Bandeau PWA" (`show_on_ticker=true`).

**Paramètre :** `?restaurant_id=1` (optionnel — filtre selon le statut du restaurant : actif, essai, expiré)

**Réponse :**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Nouvelle fonctionnalité disponible",
      "content": "Le suivi GPS en temps réel est maintenant disponible !",
      "type": "info",
      "link_url": "https://menupro.ci/nouveautes",
      "link_label": "En savoir plus",
      "is_dismissible": true
    },
    {
      "id": 2,
      "title": "Maintenance prévue ce soir",
      "content": "Le service sera indisponible de 23h à 01h.",
      "type": "warning",
      "link_url": null,
      "link_label": "En savoir plus",
      "is_dismissible": false
    }
  ]
}
```

**Champs :**

| Champ | Type | Notes |
|-------|------|-------|
| `id` | int | Identifiant pour le dismiss |
| `title` | string | Titre court |
| `content` | string | Texte du bandeau |
| `type` | string | `info` \| `success` \| `warning` \| `danger` |
| `link_url` | string\|null | URL du bouton CTA — `null` si pas de lien |
| `link_label` | string | Libellé du bouton — défaut `"En savoir plus"` |
| `is_dismissible` | bool | Si `true`, afficher un bouton de fermeture |

**Couleurs suggérées par type :**

| Type | Couleur fond | Couleur texte |
|------|-------------|--------------|
| `info` | `#EFF6FF` | `#1D4ED8` |
| `success` | `#F0FDF4` | `#15803D` |
| `warning` | `#FFFBEB` | `#92400E` |
| `danger` | `#FEF2F2` | `#B91C1C` |

---

### `POST /api/v1/ticker/{id}/dismiss` — [Auth]
Rate limit : 60/min

Masque une annonce définitivement pour l'utilisateur connecté (idempotent — appeler plusieurs fois ne cause pas d'erreur).

Retourne HTTP **422** si `is_dismissible=false` (l'annonce ne peut pas être fermée).

```json
{ "message": "Annonce masquée." }
```

**Implémentation recommandée dans l'app :**
1. Au démarrage → `GET /ticker` → stocker les IDs déjà fermés en local (AsyncStorage/SharedPreferences)
2. Filtrer côté app les annonces dont l'ID est dans la liste locale
3. Quand l'utilisateur ferme → `POST /ticker/{id}/dismiss` + ajouter l'ID en local
4. Les annonces avec `is_dismissible=false` n'affichent pas de bouton de fermeture

---

## 10. Endpoints API — Bannières promotionnelles

Les bannières sont des **images publicitaires** affichées en carousel sur la page menu d'un restaurant. Elles sont créées par le Super Admin — certaines sont globales (tous les restaurants), d'autres ciblées sur un restaurant précis.

### `GET /api/v1/restaurants/{restaurantId}/banners` — Public

Rate limit : 120/min par IP. Pas d'authentification requise.

Retourne les bannières actives pour ce restaurant (globales + spécifiques au restaurant), triées par `sort_order` croissant.

**Réponse 200 :**
```json
{
  "data": [
    {
      "id": 1,
      "title": "-20% sur les pizzas",
      "subtitle": "Ce weekend seulement",
      "image_url": "https://menupro.ci/storage/banners/pizza-promo.jpg",
      "link_type": "promo_code",
      "link_value": "PIZZA20",
      "cta_label": "Utiliser le code"
    },
    {
      "id": 2,
      "title": null,
      "subtitle": null,
      "image_url": "https://menupro.ci/storage/banners/global-pub.jpg",
      "link_type": "none",
      "link_value": null,
      "cta_label": null
    }
  ]
}
```

**Champs :**

| Champ | Type | Notes |
|-------|------|-------|
| `id` | int | Identifiant |
| `title` | string\|null | Texte overlay titre — `null` = image seule |
| `subtitle` | string\|null | Texte overlay sous-titre |
| `image_url` | string | URL complète de l'image (ratio 16:7 recommandé) |
| `link_type` | string | `none` \| `dish` \| `promo_code` \| `url` |
| `link_value` | string\|null | Valeur selon `link_type` (ID plat, code, URL) |
| `cta_label` | string\|null | Libellé du bouton CTA — `null` si pas de bouton |

**Comportement selon `link_type` :**

| `link_type` | Action au clic |
|-------------|---------------|
| `none` | Aucune — bannière décorative |
| `dish` | `link_value` = ID du plat → naviguer vers ce plat dans le menu |
| `promo_code` | `link_value` = code promo → l'appliquer automatiquement au panier |
| `url` | `link_value` = URL externe → ouvrir dans le navigateur |

**Implémentation recommandée :**
- Afficher en carousel horizontal en haut de la page menu
- Si `data` est vide → ne pas afficher le carousel (pas d'espace vide)
- Si `title` ou `subtitle` → afficher en overlay sur l'image avec fond dégradé
- Si `cta_label` → afficher un bouton en bas de l'image

**Erreurs :**

| Code | Cas |
|------|-----|
| 404 | `restaurant_id` inconnu |

---

## 11. Routes web publiques

> Pour référence — le menu web est accessible via QR code au restaurant.
> Préfixe : `https://menupro.ci/r/{slug}`

| URL | Description |
|-----|-------------|
| `/r/{slug}` | Page menu (Livewire) |
| `/r/{slug}/commander` | Checkout web (Livewire) |
| `/r/{slug}/commande/{token}` | Suivi de commande (HTML) |
| `/r/{slug}/commande/{token}/json` | Suivi (JSON polling) — voir §16 |
| `/r/{slug}/commande/{token}/recu` | Reçu PDF |
| `/r/{slug}/commande/{token}/avis` | Formulaire d'avis |

---

## 12. Flux complet de commande

```
CLIENT ouvre l'app
        │
        ▼
GET /restaurants?city=Abidjan&lat=5.35&lng=-4.00
        │
        ▼
GET /restaurants/{id}/menu
        │ (client sélectionne des plats)
        ▼
GET /restaurants/{id}/delivery-estimate?lat=...&lng=...
        │ within_range=true
        ▼
POST /client/orders
     { restaurant_id, items, delivery_lat, delivery_lng, ... }
        │
        ▼  Réponse 201
     { order_id: 42, tracking_token: "...", next_step: "payment" }
        │
        ▼
POST /client/payment/42/initiate
        │
        ▼  { payment_url }
     Ouvrir Wave WebView
        │
        ▼  Paiement validé / annulé
GET /client/payment/success?token=... (ou /error)
        │
        ▼
GET /client/payment/42/status   ← polling jusqu'à payment_status=completed
        │
        ▼  order.status = "paid"
     Push FCM: "Commande confirmée ✅"
        │
        ▼
GET /client/orders/track/{token}  ← polling suivi temps réel
     order_status: confirmed → preparing → ready → delivering → completed
     delivery.status: pending → assigned → heading_to_restaurant → ... → delivered
```

---

## 13. Formats de réponse détaillés

### 11.1 OrderObject

```json
{
  "id": 42,
  "reference": "PLT-AB12CD34",
  "tracking_token": "a1b2c3d4e5f6a1b2c3d4e5f6a1b2c3d4",
  "status": "preparing",
  "status_label": "En préparation",
  "payment_status": "completed",
  "payment_method": "wave",
  "subtotal": 250000,
  "delivery_fee": 75000,
  "discount_amount": 0,
  "total": 325000,
  "estimated_minutes": 45,
  "items": [
    {
      "name": "Attiéké Poisson",
      "quantity": 2,
      "unit_price": 125000,
      "total": 250000
    }
  ],
  "created_at": "2026-07-17T10:30:00.000000Z"
}
```

---

### 11.2 CustomerObject

```json
{
  "id": 5,
  "name": "Jean Dupont",
  "email": "jean@example.com",
  "phone": "+2250701234567",
  "city": "Abidjan",
  "total_orders": 12
}
```

---

## 14. Structure des options de plats

Les groupes d'options permettent de personnaliser un plat (taille, extras, sauces, etc.).

### 12.1 Modèle de données

```
Dish ──── (many-to-many) ──── DishOptionGroup ──── (has-many) ──── DishOption
```

### 12.2 Exemple complet

```json
{
  "id": 15,
  "name": "Burger Classic",
  "price": 150000,
  "option_groups": [
    {
      "id": 3,
      "name": "Taille",
      "is_required": true,
      "min_selections": 1,
      "max_selections": 1,
      "options": [
        { "id": 8, "name": "Normal", "price_adjustment": 0, "is_default": true },
        { "id": 9, "name": "XXL", "price_adjustment": 50000, "is_default": false }
      ]
    },
    {
      "id": 4,
      "name": "Extras",
      "is_required": false,
      "min_selections": 0,
      "max_selections": 3,
      "options": [
        { "id": 10, "name": "Fromage", "price_adjustment": 20000, "is_default": false },
        { "id": 11, "name": "Bacon", "price_adjustment": 30000, "is_default": false },
        { "id": 12, "name": "Œuf", "price_adjustment": 15000, "is_default": false }
      ]
    }
  ]
}
```

### 12.3 Calcul du prix

```
unit_price  = dish.price + sum(selected_options[].price_adjustment)
total_price = unit_price × quantity
```

### 12.4 Règles de validation côté app

| Groupe | Règle |
|--------|-------|
| `is_required=true` | L'utilisateur doit sélectionner au moins `min_selections` options |
| `max_selections=1` | Sélection unique (radio) |
| `max_selections>1` | Sélection multiple (checkboxes), max `max_selections` items |
| `is_default=true` | Présélectionner cette option par défaut |

---

## 15. Horaires d'ouverture

### 13.1 Format JSON dans `opening_hours`

```json
{
  "monday":    { "is_open": true,  "open": "08:00", "close": "22:00" },
  "tuesday":   { "is_open": true,  "open": "08:00", "close": "22:00" },
  "wednesday": { "is_open": true,  "open": "08:00", "close": "22:00" },
  "thursday":  { "is_open": true,  "open": "08:00", "close": "22:00" },
  "friday":    { "is_open": true,  "open": "08:00", "close": "23:00" },
  "saturday":  { "is_open": true,  "open": "10:00", "close": "23:00" },
  "sunday":    { "is_open": false, "open": "00:00", "close": "00:00" }
}
```

**Clés :** `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday`

### 13.2 Règles d'interprétation

- Si `opening_hours` est `null` → restaurant ouvert par défaut
- Timezone : `Africa/Abidjan` (UTC+0)
- Fermeture après minuit : si `close < open` → ouvert si `heure >= open OU heure <= close`
- `is_open: false` → fermé ce jour-là, indépendamment des horaires

### 13.3 Message de prochaine ouverture

Exemples de valeurs possibles du champ `next_opening_message` :
- `"Aujourd'hui à 08:00"`
- `"Demain (Lundi) à 08:00"`
- `"Mercredi 22/07 à 08:00"`

---

## 16. Paiement — Wave & Paiement à la livraison

### 15.1 Méthodes de paiement disponibles

| Méthode | Valeur API | Disponible | Conditions |
|---------|-----------|------------|-----------|
| Wave Mobile Money | `wave` | **Oui** | Toujours disponible via l'API plateforme |
| Paiement à la livraison | `cash_on_delivery` | **Oui** | Si `restaurant.cash_on_delivery=true` |
| Orange Money | `orange_money` | Non (prévu) | Retourne 422 actuellement |
| MTN Money | `mtn_money` | Non (prévu) | Retourne 422 actuellement |
| Espèces sur place | `cash` | Web seulement | Checkout web QR code |

---

### 15.2 Wave Mobile Money

#### Configuration serveur
- `WAVE_API_KEY` — clé API Wave Business
- `WAVE_WEBHOOK_SECRET` — secret de vérification des webhooks

#### Modes de collecte

| Mode | Condition | Description |
|------|-----------|-------------|
| Plateforme (défaut) | `restaurant.wave_business_enabled=false` | Fonds collectés sur le compte Wave de menupro.ci, redistribués via wallet |
| Restaurant direct | `restaurant.wave_business_enabled=true` | Paiement direct sur le compte Wave Business du restaurant |

#### Flux d'intégration

```
1. POST /client/orders
   → { order_id: 42, tracking_token: "...", next_step: "payment" }

2. POST /client/payment/42/initiate
   → { payment_url: "https://pay.wave.com/checkout/cs_xxxxx", amount: 325000 }

3. Ouvrir payment_url dans WebView ou navigateur système

4. L'utilisateur paye sur Wave puis est redirigé :
   Succès → GET /client/payment/success?token={tracking_token}
   Échec  → GET /client/payment/error?token={tracking_token}

5. Webhook Wave (asynchrone, côté serveur) confirme définitivement

6. Polling de vérification :
   GET /client/payment/42/status → { payment_status: "completed" }
```

**Important :** Ne jamais afficher "payé" uniquement sur la base du callback (étape 4). Toujours confirmer via polling (étape 6) ou notification push.

#### Webhook Wave (géré côté serveur)
Route : `POST /webhooks/wave`
Événements :
- `checkout.session.completed` → commande payée, notifications envoyées
- `checkout.session.payment_failed` → échec loggé

Sécurité : signature HMAC-SHA256 (header `Wave-Signature: t={ts},v1={sig}`, fenêtre 5 min).

---

### 15.3 Paiement à la livraison (cash on delivery)

Disponible si `restaurant.cash_on_delivery = true`.

#### Vérifier la disponibilité

Dans la réponse `GET /restaurants/{id}` :
```json
{
  "cash_on_delivery": true
}
```

#### Commande avec paiement à la livraison

```json
POST /api/v1/client/orders
{
  "restaurant_id": 1,
  "items": [...],
  "delivery_lat": 5.3612,
  "delivery_lng": -3.9814,
  "delivery_address": "Riviera 3, Apt 12",
  "delivery_city": "Abidjan",
  "payment_method": "cash_on_delivery"
}
```

**Réponse 201 :**
```json
{
  "order": {
    "id": 42,
    "status": "confirmed",
    "payment_method": "cash_on_delivery",
    "payment_status": "pending",
    "total": 325000
  },
  "tracking_token": "...",
  "next_step": "track"
}
```

`next_step: "track"` (pas `"payment"`) — pas de redirection Wave, on suit directement la commande.

#### Cycle de vie du paiement à la livraison

```
Commande créée → status: confirmed (payment_status: pending)
        ↓  [directement, pas d'étape paiement]
Livreur assigné → status: delivering
        ↓
Livreur récupère → status: picked_up
        ↓
Livreur livre et encaisse le cash
        ↓
Livreur marque "delivered" → order.status: completed
                              payment_status: completed  (auto)
```

Le paiement est marqué `completed` automatiquement quand la livraison est marquée `delivered`.

#### Affichage recommandé dans l'app

Pour une commande `cash_on_delivery` :
- Afficher le badge "Paiement à la livraison" avec icône cash
- Sur la page de suivi : rappeler le montant à payer au livreur
- Ne pas afficher de bouton "Payer maintenant"

---

### 15.4 Montants

Les montants dans l'API sont en **FCFA entiers** (ex: `325000` = 3 250 F CFA). La conversion centimes ↔ FCFA est faite côté serveur pour Wave.

---

## 17. Notifications push FCM

### 15.1 Configuration requise

Utilise l'**API FCM HTTP v1** (OAuth2 JWT).
La clé serveur legacy est désactivée depuis juin 2024.

Requis côté serveur :
- `firebase_project_id`
- `firebase_service_account_json` (JSON complet du service account Firebase)

### 15.2 Enregistrement du token

```
PATCH /api/v1/client/auth/fcm-token
{ "fcm_token": "fXxXxXxXxXxX..." }

DELETE /api/v1/client/auth/fcm-token   ← à la déconnexion
```

### 15.3 Notifications reçues par le client

| Déclencheur | Titre | Corps |
|------------|-------|-------|
| Commande confirmée | "Commande confirmée ✅" | "Votre commande #42 est en préparation chez {restaurant}." |
| En préparation | "En préparation 👨‍🍳" | "{restaurant} prépare votre commande #42." |
| Commande prête | "Commande prête 📦" | "Votre commande #42 est prête, un livreur va la récupérer." |
| Livreur en route | "Livreur en route 🛵" | "Votre commande #42 est en chemin !" |
| Livraison effectuée | "Livraison effectuée 🎉" | "Votre commande #42 a été livrée. Bon appétit !" |
| Commande annulée | "Commande annulée ❌" | "Votre commande #42 a été annulée." |

**Payload data inclus dans chaque notification :**
```json
{
  "type": "order_status",
  "order_id": "42",
  "status": "confirmed"
}
```

Utiliser `type` pour router la notification dans l'app (naviguer vers la page de suivi).

---

## 18. Suivi de commande temps réel

### 16.1 Polling JSON recommandé

```
GET /r/{slug}/commande/{tracking_token}/json
```

Fréquence recommandée : toutes les 10 secondes tant que `is_final=false`.

**Réponse complète :**
```json
{
  "status": "preparing",
  "status_label": "En préparation",
  "status_color": "primary",
  "payment_status": "completed",
  "estimated_ready_at": "2026-07-17T11:15:00.000Z",
  "is_final": false,
  "can_be_modified": false,
  "remaining_modification_time": null,
  "has_review": false,
  "review_url": null,
  "driver_lat": null,
  "driver_lng": null,
  "driver_status_label": null,
  "progress": [
    { "key": "placed",    "label": "Commande passée",      "completed": true,  "current": false, "time": "2026-07-17T10:30:00Z" },
    { "key": "paid",      "label": "Paiement confirmé",    "completed": true,  "current": false, "time": "2026-07-17T10:32:00Z" },
    { "key": "confirmed", "label": "Commande confirmée",   "completed": true,  "current": false, "time": "2026-07-17T10:33:00Z" },
    { "key": "preparing", "label": "En préparation",       "completed": false, "current": true,  "time": null },
    { "key": "ready",     "label": "En livraison",         "completed": false, "current": false, "time": null },
    { "key": "completed", "label": "Livrée",               "completed": false, "current": false, "time": null }
  ]
}
```

### 16.2 Position GPS livreur

Quand `driver_lat` et `driver_lng` sont non null → afficher la position sur une carte.
Ces valeurs sont mises à jour toutes les ~2 secondes (rate limit 30/min côté livreur).

### 16.3 Modification de commande

Possible tant que `can_be_modified=true` (jusqu'à 5 min après le paiement).
`remaining_modification_time` indique le nombre de minutes restantes.

### 16.4 Fin du suivi

Arrêter le polling quand `is_final=true` (statut `completed`, `cancelled` ou `refunded`).

Si `has_review=true` → l'avis a déjà été déposé. Sinon afficher `review_url`.

---

## 19. Calcul des frais de livraison

### 17.1 Formule

```
distance_fee = distance_km × delivery_fee_per_km
raw_fee      = delivery_base_fee + distance_fee

Si heure de pointe (11h-14h ou 18h-21h) :
  fee = round(raw_fee × (1 + peak_surcharge_percent / 100))
Sinon :
  fee = raw_fee
```

### 17.2 Valeurs par défaut (Abidjan)

| Paramètre | Valeur |
|-----------|--------|
| `delivery_base_fee` | 500 FCFA (50 000 centimes) |
| `delivery_fee_per_km` | 150 FCFA/km (15 000 centimes) |
| `peak_surcharge_percent` | 20% |
| `max_delivery_distance_km` | 25 km |

### 17.3 Exemple

```
Distance : 4 km — heure normale

fee = 500 + (4 × 150) = 1 100 FCFA

En heure de pointe :
fee = 1 100 × 1.20 = 1 320 FCFA
```

### 17.4 Temps de livraison estimé

```
transit_minutes = ceil(distance_km / 25 * 60)   ← vitesse moto ville : 25 km/h
prep_minutes    = restaurant.avg_prep_time ?? 20
total           = prep_minutes + transit_minutes
```

---

## 20. Statuts & enums

### 18.1 OrderStatus

| Valeur | Label FR | `isActive()` | `isFinal()` | `canBeCancelled()` |
|--------|----------|-------------|------------|-------------------|
| `draft` | Brouillon | false | false | true |
| `pending_payment` | En attente de paiement | false | false | true |
| `paid` | Payée | true | false | true |
| `confirmed` | Confirmée | true | false | true |
| `preparing` | En préparation | true | false | false |
| `ready` | Prête | true | false | false |
| `delivering` | En livraison | true | false | false |
| `completed` | Terminée | false | **true** | false |
| `cancelled` | Annulée | false | **true** | false |
| `refunded` | Remboursée | false | **true** | false |

**Transitions autorisées :**
```
draft           → pending_payment, cancelled
pending_payment → paid, cancelled
paid            → confirmed, cancelled, refunded
confirmed       → preparing, cancelled
preparing       → ready
ready           → delivering, completed
delivering      → completed
completed       → refunded
```

---

### 18.2 DeliveryStatus

| Valeur | Label FR |
|--------|----------|
| `pending` | En attente de livreur |
| `assigned` | Livreur assigné |
| `heading_to_restaurant` | En route vers le restaurant |
| `picked_up` | Commande récupérée |
| `delivering` | En livraison |
| `delivered` | Livrée |
| `cancelled` | Annulée |

---

### 18.3 PaymentStatus

| Valeur | Label FR |
|--------|----------|
| `pending` | En attente |
| `pending_verification` | En vérification |
| `processing` | En cours |
| `completed` | Complété |
| `failed` | Échoué |
| `refunded` | Remboursé |

---

### 18.4 OrderType

| Valeur | Label FR | Adresse requise |
|--------|----------|----------------|
| `dine_in` | Sur place | Non |
| `takeaway` | À emporter | Non |
| `delivery` | Livraison | **Oui** |

---

## 21. Modèle de données

### 19.1 Table `customers`

| Champ | Type | Notes |
|-------|------|-------|
| `id` | int | PK |
| `user_id` | int | FK → `users` |
| `phone` | string | Identifiant de connexion |
| `city` | string\|null | |
| `default_delivery_address` | string\|null | Adresse textuelle par défaut |
| `default_latitude` | decimal(10,7)\|null | |
| `default_longitude` | decimal(10,7)\|null | |
| `avatar_path` | string\|null | |
| `is_active` | boolean | `true` = compte actif |
| `total_orders` | integer | Compteur incrémenté à chaque commande |
| `last_order_at` | datetime\|null | |
| `fcm_token` | string\|null | Token FCM v1 pour push notifications |

---

### 19.2 Table `customer_addresses`

| Champ | Type | Notes |
|-------|------|-------|
| `id` | int | PK |
| `customer_id` | int | FK → `customers` |
| `label` | string | Ex: "Maison", "Bureau" |
| `address` | string | |
| `city` | string | |
| `zone` | string\|null | Quartier |
| `latitude` | decimal(10,7)\|null | |
| `longitude` | decimal(10,7)\|null | |
| `instructions` | string\|null | |
| `is_default` | boolean | |

---

### 19.3 Table `orders` (champs pertinents client)

| Champ | Type | Notes |
|-------|------|-------|
| `reference` | string | Format `PLT-XXXXXXXX` (plateforme) ou `CMD-YYMMDD-XXXX` (web) |
| `tracking_token` | string(32) | Token URL de suivi — stable pour la vie de la commande |
| `customer_id` | int\|null | FK → `customers` |
| `type` | OrderType | `delivery` uniquement via API plateforme |
| `status` | OrderStatus | |
| `source` | string | `platform_web`, `platform_app`, `pos` |
| `subtotal` | integer | En FCFA |
| `delivery_fee` | integer | |
| `discount_amount` | integer | |
| `total` | integer | |
| `platform_commission` | integer | 12% du subtotal |
| `payment_status` | PaymentStatus | |
| `payment_method` | string\|null | `wave`, `cash`, etc. |
| `delivery_latitude` | decimal(10,8) | |
| `delivery_longitude` | decimal(11,8) | |
| `delivery_address` | text\|null | |
| `estimated_prep_time` | integer | Minutes |
| `confirmed_at` | datetime\|null | |
| `preparing_at` | datetime\|null | |
| `ready_at` | datetime\|null | |
| `picked_up_at` | datetime\|null | |
| `completed_at` | datetime\|null | |
| `cancelled_at` | datetime\|null | |

---

### 19.4 Table `order_items`

| Champ | Type | Notes |
|-------|------|-------|
| `dish_name` | string | Snapshot du nom au moment de la commande |
| `unit_price` | integer | `dish.price + options_price` |
| `quantity` | integer | |
| `total_price` | integer | `unit_price × quantity` |
| `selected_options` | json | `[{id, name, price_adjustment}]` |
| `options_price` | integer | Somme des ajustements d'options |
| `special_instructions` | string\|null | |

---

### 19.5 Table `restaurants` (champs publics)

| Champ | Notes |
|-------|-------|
| `slug` | Identifiant URL — `https://menupro.ci/r/{slug}` |
| `logo_url` | URL complète via `https://menupro.ci/storage/{logo_path}` |
| `banner_url` | URL complète |
| `primary_color` | Couleur hex du thème (ex: `"#FF5733"`) |
| `secondary_color` | |
| `opening_hours` | JSON — voir §13 |
| `min_order_amount` | En FCFA |
| `avg_prep_time_minutes` | Minutes, défaut 20 |
| `is_on_platform` | `true` si visible dans l'app livraison |
| `platform_category` | Ex: `ivoirien`, `pizza`, `fastfood`, `burger` |
| `cash_on_delivery` | `true` si paiement cash accepté |
| `payment_methods` | Array — ex: `["wave","cash_on_delivery"]` — méthodes disponibles pour ce restaurant |
| `reservations_enabled` | |

---

### 19.6 Table `dishes`

| Champ | Notes |
|-------|-------|
| `price` | En FCFA |
| `compare_price` | Prix barré (null si pas de promo) |
| `image_url` | `https://menupro.ci/storage/{image_path}` |
| `is_available` | `is_active && is_in_stock` |
| `is_featured` | Badge "Populaire" |
| `is_new` | Badge "Nouveau" |
| `is_spicy` | Badge "Épicé" |
| `is_vegetarian` | Badge "Végétarien" |
| `allergens` | Array de strings |
| `prep_time` | Minutes estimées |
| `calories` | Nullable |

`discount_percentage` = `round((compare_price - price) / compare_price × 100)` si `compare_price` non null.

---

### 19.7 Table `promo_codes`

| Champ | Notes |
|-------|-------|
| `code` | Toujours en MAJUSCULES |
| `discount_type` | `percentage` ou `fixed` |
| `discount_value` | Pourcentage (0-100) ou FCFA |
| `min_order_amount` | Minimum commande pour activer |
| `max_discount_amount` | Plafond remise (pour `percentage`) |
| `expires_at` | Date d'expiration |

Application dans le checkout web :
```
Saisir le code → POST (validation) → réduction appliquée au subtotal
```

---

## 22. Rate limits

| Endpoint | Limite |
|----------|--------|
| `POST /client/auth/login` | 5/min (IP) + 5/15min (compte) |
| `POST /client/auth/register` | 5/heure par IP |
| `POST /client/orders` | 20/heure par client |
| `POST /client/payment/{id}/initiate` | 5/5min par client |
| `GET /restaurants*` | 120/min par IP |
| `GET /orders/track/{token}` | 120/min par IP |
| `GET /ticker` | 120/min par IP |
| `POST /ticker/{id}/dismiss` | 60/min par client |

Toutes les erreurs de rate limit → HTTP **429** JSON.

---

## 23. Gestion des erreurs

### Headers requis

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}    ← routes protégées uniquement
```

### Format standard des erreurs

```json
// 401 — Token invalide ou expiré
{ "message": "Unauthenticated." }

// 403 — Compte suspendu
{ "message": "Compte suspendu. Contactez le support." }

// 404 — Ressource introuvable
{ "message": "No query results for model [Restaurant]." }

// 422 — Erreur de validation
{
  "message": "The phone field is required.",
  "errors": {
    "phone": ["The phone field is required."],
    "password": ["The password must be at least 6 characters."]
  }
}

// 422 — Hors zone de livraison
{
  "message": "Ce restaurant ne livre pas à cette adresse.",
  "distance_km": 18.5
}

// 429 — Rate limit
{
  "message": "Too Many Requests.",
  "retry_after": 60
}
```

### Stratégie recommandée

| Code | Action |
|------|--------|
| 401 | Supprimer le token local → rediriger vers login |
| 403 | Afficher le message d'erreur |
| 404 | Afficher "non trouvé" |
| 422 | Afficher les erreurs de champs |
| 429 | Attendre `retry_after` secondes |
| 500 | Afficher message générique + retry |

---

## 24. Notes importantes

1. **Tous les montants sont en FCFA entiers** dans l'API (ex: `125000` = 1 250 F CFA). Toujours formater avec séparateur de milliers avant affichage.

2. **L'API REST n'inclut pas les options de plats** dans `GET /restaurants/{id}/menu`. Pour les extras/tailles, les pages Livewire web les affichent — si l'app mobile a besoin des options, enrichir le controller menu (endpoint à créer ou extension de l'existant).

3. **Seul `wave` est supporté** côté API plateforme actuellement. `orange_money`, `mtn_money`, `cash` sont validés mais retournent 422.

4. **Le webhook Wave confirme définitivement** le paiement — ne jamais afficher "payé" uniquement sur la base du callback GET.

5. **Le `tracking_token` est stable** sur toute la vie de la commande (32 caractères hex). L'utiliser pour le suivi sans auth (URL de partage par SMS par exemple).

6. **Référence de commande** : `PLT-XXXXXXXX` pour les commandes plateforme (app mobile), `CMD-YYMMDD-XXXX` pour les commandes web (QR code restaurant).

7. **Images** : les `logo_path`, `banner_path`, `image_path` sont des chemins relatifs. URL complète : `https://menupro.ci/storage/{path}`.

8. **Modification de commande** : possible jusqu'à 5 minutes après paiement (`can_be_modified=true`). Après, la commande est définitive côté client.

9. **FCM v1 uniquement** — l'ancienne API legacy FCM est désactivée depuis juin 2024. Le token FCM doit être enregistré après chaque connexion.

10. **Heures de pointe** : 11h–14h et 18h–21h (timezone `Africa/Abidjan`). Les frais de livraison augmentent de 20%.

---

*Ce document est généré depuis le code source de MenuPro — branche `main` — commit `c6abc81`.*

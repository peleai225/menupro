# MenuPro — Référence complète : Application Livreur

> Document de référence pour le développement de l'application mobile livreur (PWA / React Native / Flutter).
> Dernière mise à jour : 2026-07-17

---

## Table des matières

1. [Architecture générale](#1-architecture-générale)
2. [Authentification & accès](#2-authentification--accès)
3. [Endpoints API — Livreur](#3-endpoints-api--livreur)
4. [Endpoints API — Restaurant](#4-endpoints-api--restaurant)
5. [Endpoints API — Admin](#5-endpoints-api--admin)
6. [Endpoints API — Ticker (annonces)](#6-endpoints-api--ticker-annonces)
7. [Formats de réponse détaillés](#7-formats-de-réponse-détaillés)
8. [Flux de vie d'une livraison](#8-flux-de-vie-dune-livraison)
9. [Statuts & enums](#9-statuts--enums)
10. [WebSocket — Canaux & événements temps réel](#10-websocket--canaux--événements-temps-réel)
11. [Notifications push (FCM)](#11-notifications-push-fcm)
12. [Modèle de données](#12-modèle-de-données)
13. [Calcul des tarifs & commission](#13-calcul-des-tarifs--commission)
14. [Rate limits](#14-rate-limits)
15. [Headers & middleware globaux](#15-headers--middleware-globaux)
16. [Schéma base de données](#16-schéma-base-de-données)

---

## 1. Architecture générale

```
Base URL         : https://menupro.ci/api/v1
Format           : JSON exclusivement (Content-Type: application/json)
Auth             : Bearer token (Laravel Sanctum)
Monnaie          : XOF (Franc CFA) — toutes les sommes en centimes entiers
                   Exemple : 500 FCFA = 50000 (centimes)
Langue           : Français (messages d'erreur)
GPS              : WGS84 (latitude/longitude décimaux)
```

### Middleware globaux (toutes les routes `/api/v1/`)

| Middleware | Alias | Rôle |
|-----------|-------|------|
| `ForceJsonResponse` | `api.json` | Force `Accept: application/json` — toujours du JSON en retour |
| `SanitizeApiInput` | `api.sanitize` | `trim()` + `strip_tags()` sur tous les inputs (sauf `password`, `fcm_token`, `token`, `secret`) |
| `ApiSecurityHeaders` | `api.security` | Headers de sécurité HTTP (CORS, X-Frame-Options, etc.) |

---

## 2. Authentification & accès

### 2.1 Inscription livreur

```
POST /api/v1/driver/auth/register
Rate limit : 5 requêtes/heure par IP
```

**Corps (multipart/form-data — fichiers requis) :**

| Champ | Type | Règles |
|-------|------|--------|
| `name` | string | required, max:100 |
| `phone` | string | required, max:20, unique |
| `email` | string | nullable, email, max:150, unique |
| `password` | string | required, min:6 |
| `city` | string | required, max:100 |
| `zone` | string | nullable, max:100 |
| `vehicle_type` | string | required, `in:moto,vélo,voiture` |
| `vehicle_plate` | string | nullable, max:20 |
| `cni_number` | string | required, max:30 |
| `cni_photo` | file image | required, max:5 Mo |
| `license_photo` | file image | required, max:5 Mo |
| `vehicle_photo` | file image | required, max:5 Mo |

**Réponse 201 :**
```json
{ "message": "Compte créé. En attente de validation." }
```
Pas de token retourné — le compte doit être approuvé avant de pouvoir se connecter.

---

### 2.2 Connexion livreur

```
POST /api/v1/driver/auth/login
Rate limit : 5/min par IP + 5/15min par compte
```

**Corps :**
```json
{ "phone": "0500000000", "password": "secret" }
```

**Réponse 200 :**
```json
{
  "token": "1|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "driver": { /* DriverObject — voir §7.1 */ }
}
```

**Erreurs :**
| Code | Condition |
|------|-----------|
| 401 | Identifiants invalides |
| 403 | Compte `rejected` (message: "Votre compte a été rejeté.") |
| 403 | Compte `suspended` (message: "Votre compte a été suspendu.") |

Note : un compte `pending` peut se connecter mais est bloqué par le middleware sur toutes les routes protégées.

---

### 2.3 Utilisation du token

Toutes les routes marquées **[Auth]** nécessitent :
```
Authorization: Bearer {token}
```

---

### 2.4 Middleware `delivery.driver`

Appliqué à toutes les routes protégées livreur. Vérifie dans l'ordre :
1. Utilisateur authentifié avec `role = delivery_driver` → sinon 403
2. Profil `DeliveryDriver` existe → sinon 403
3. `verification_status = approved` → sinon 403 avec `{ verification_status: "pending"|"approved"|... }`

---

## 3. Endpoints API — Livreur

### 3.1 Profil

#### `GET /api/v1/driver/auth/me` [Auth]
Retourne le profil du livreur connecté.
```json
{ /* DriverObject — voir §7.1 */ }
```

#### `POST /api/v1/driver/auth/logout` [Auth]
Révoque le token courant.
```json
{ "message": "Déconnecté avec succès." }
```

#### `PATCH /api/v1/driver/auth/fcm-token` [Auth]
Enregistre ou met à jour le token FCM pour les notifications push.
```json
{ "fcm_token": "fxxxxxxxxxxxxxxxxxxxxx" }
```
Réponse : `{ "message": "Token FCM mis à jour." }`

---

### 3.2 Disponibilité

#### `POST /api/v1/driver/status` [Auth]
Passe en ligne / hors ligne.
```json
{ "online": true }
```
Réponse :
```json
{
  "is_available": true,
  "message": "Vous êtes maintenant en ligne."
}
```

---

### 3.3 Position GPS

#### `PATCH /api/v1/driver/location` [Auth]
Rate limit : **30 requêtes/minute** par livreur.

```json
{
  "latitude": 5.3534,
  "longitude": -4.0012,
  "accuracy": 8.5,
  "speed": 23.4,
  "heading": 142.0
}
```

| Champ | Type | Règles |
|-------|------|--------|
| `latitude` | numeric | required, between:-90,90 |
| `longitude` | numeric | required, between:-180,180 |
| `accuracy` | numeric | nullable, min:0 (mètres) |
| `speed` | numeric | nullable, min:0 (km/h) |
| `heading` | numeric | nullable, between:0,360 (degrés) |

- Met à jour `latitude/longitude/location_updated_at` sur le profil driver.
- Crée une entrée dans `driver_locations` (historique GPS).
- Si livraison active : met à jour `driver_latitude/longitude` sur la `Delivery` ET broadcast `DriverLocationUpdated` (canal privé `delivery.{id}`).

---

### 3.4 Courses disponibles

#### `GET /api/v1/driver/deliveries/pending` [Auth]
Retourne les courses `status=pending` dans la même ville que le livreur.
**Requiert `is_available=true`** — retourne `[]` si le livreur est hors ligne.

```json
{
  "data": [
    { /* DeliveryListItem — voir §7.2 */ }
  ]
}
```

---

### 3.5 Course active

#### `GET /api/v1/driver/deliveries/active` [Auth]
Retourne la livraison en cours (statuts : `assigned`, `heading_to_restaurant`, `picked_up`, `delivering`) ou `null`.

```json
{
  "data": { /* DeliveryDetail — voir §7.3 */ }
}
```
ou
```json
{ "data": null }
```

---

### 3.6 Accepter une course

#### `POST /api/v1/driver/deliveries/{id}/accept` [Auth]

Conditions requises :
- Livreur `is_available=true`
- Pas de livraison déjà active
- Course `status=pending`

Transaction atomique :
- `delivery.driver_id = driver.id`
- `delivery.status = assigned`
- `delivery.assigned_at = now()`
- `driver.is_available = false`
- `order.driver_assigned_at = now()`

Réponse 200 :
```json
{
  "message": "Course acceptée.",
  "delivery": { /* DeliveryDetail — voir §7.3 */ }
}
```

Erreurs :
| Code | Condition |
|------|-----------|
| 409 | Livreur a déjà une course active |
| 409 | Course déjà assignée à quelqu'un d'autre |
| 422 | Livreur hors ligne (`is_available=false`) |

---

### 3.7 Décliner une course

#### `POST /api/v1/driver/deliveries/{id}/decline` [Auth]
Libère la course (remet `status=pending`, libère le driver).

```json
{ "message": "Course déclinée." }
```

---

### 3.8 Mettre à jour le statut d'une course

#### `PATCH /api/v1/driver/deliveries/{id}/status` [Auth]

```json
{ "status": "picked_up" }
```

**Transitions autorisées (strictement linéaires) :**

```
assigned  →  heading_to_restaurant
             En route vers le restaurant

heading_to_restaurant  →  picked_up
                           Commande récupérée
                           (order.picked_up_at = now())

picked_up  →  delivering
              En livraison

delivering  →  delivered
               Livraison effectuée
               (delivery.delivered_at = now(),
                order.status = completed,
                driver.is_available = true,
                DriverEarning créé)
```

Valeurs acceptées : `heading_to_restaurant` | `picked_up` | `delivering` | `delivered`

Réponse 200 :
```json
{
  "message": "Statut mis à jour.",
  "delivery": { /* DeliveryDetail */ }
}
```

Erreur 422 si transition non autorisée.

---

### 3.9 Gains

#### `GET /api/v1/driver/earnings` [Auth]
```json
{
  "available_balance": 125000,
  "today": {
    "deliveries": 3,
    "earnings": 36000
  },
  "this_week": {
    "deliveries": 14,
    "earnings": 168000
  },
  "total_lifetime": 520000
}
```
Toutes les valeurs en centimes XOF.

#### `GET /api/v1/driver/earnings/history` [Auth]
Pagine 20 entrées par page.

```json
{
  "data": [
    {
      "id": 42,
      "order_ref": "CMD-20260717-0042",
      "gross_amount": 15000,
      "platform_cut": 3000,
      "net_amount": 12000,
      "status": "available",
      "paid_at": null,
      "created_at": "2026-07-17T14:32:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "total": 87
  }
}
```

**Statuts `DriverEarning` :** `pending` → `available` → `paid`

---

### 3.10 Demande de virement

#### `POST /api/v1/driver/earnings/payout` [Auth]
Rate limit : **3 demandes/jour** par livreur.

```json
{
  "amount": 50000,
  "mobile": "0700000000",
  "payment_method": "wave"
}
```

| Champ | Règles |
|-------|--------|
| `amount` | required, integer, min:500 |
| `mobile` | required, string, max:20 |
| `payment_method` | required, `in:wave,orange_money,mtn_money` |

Note : seul `wave` est opérationnel. `orange_money` et `mtn_money` retournent 422.

Réponse 200 :
```json
{
  "message": "Virement initié avec succès.",
  "amount": 50000,
  "reference": "DRIVER-12-1721218320"
}
```

Erreurs :
| Code | Condition |
|------|-----------|
| 422 | Solde insuffisant |
| 422 | Méthode non opérationnelle |
| 429 | Limite journalière atteinte |

---

## 4. Endpoints API — Restaurant

> Middleware requis : `auth:sanctum` + `has.restaurant` + `feature:delivery`
> (Plan sans livraison → 403 JSON)

### Commandes de livraison

```
GET    /api/v1/restaurant/delivery/orders
POST   /api/v1/restaurant/delivery/orders/{id}/confirm
POST   /api/v1/restaurant/delivery/orders/{id}/ready
```

### Paramètres de livraison

```
GET    /api/v1/restaurant/delivery/settings
PATCH  /api/v1/restaurant/delivery/settings
```

---

## 5. Endpoints API — Admin

> Middleware requis : `auth:sanctum` + `super.admin` + `throttle:api.admin`

### Gestion des livreurs

```
GET    /api/v1/admin/drivers                        Liste paginée
GET    /api/v1/admin/drivers/{id}                   Détail
POST   /api/v1/admin/drivers/{id}/approve           Approuver (pending → approved)
POST   /api/v1/admin/drivers/{id}/reject            Rejeter
POST   /api/v1/admin/drivers/{id}/suspend           Suspendre
POST   /api/v1/admin/drivers/{id}/reactivate        Réactiver
```

### Restaurants plateforme

```
GET    /api/v1/admin/platform/restaurants
POST   /api/v1/admin/platform/restaurants/{id}/enable
```

---

## 6. Endpoints API — Ticker (annonces)

### `GET /api/v1/ticker`
Public, sans authentification.
Rate limit : 120/min par IP.

**Paramètres query :**
- `restaurant_id` (optionnel) — filtre les annonces selon le statut du restaurant (actif/essai/expiré)

**Réponse 200 :**
```json
{
  "data": [
    {
      "id": 1,
      "title": "Nouvelle fonctionnalité disponible",
      "content": "Découvrez le suivi GPS en temps réel.",
      "type": "info",
      "link_url": "https://menupro.ci/nouveautes",
      "link_label": "En savoir plus",
      "is_dismissible": true
    }
  ]
}
```

**Types :** `info` | `success` | `warning` | `danger`

---

### `POST /api/v1/ticker/{id}/dismiss` [Auth]
Rate limit : 60/min.

Masque une annonce pour l'utilisateur connecté (idempotent).

Erreur 422 si `is_dismissible = false`.

```json
{ "message": "Annonce masquée." }
```

---

## 7. Formats de réponse détaillés

### 7.1 DriverObject

```json
{
  "id": 12,
  "name": "Kouamé Yao",
  "phone": "0701234567",
  "city": "Abidjan",
  "zone": "Cocody",
  "vehicle_type": "moto",
  "vehicle_plate": "AB 1234 CI",
  "verification_status": "approved",
  "is_active": true,
  "is_available": true,
  "rating": 4.82,
  "total_deliveries": 147,
  "total_earnings_xof": 1764000
}
```

---

### 7.2 DeliveryListItem (courses disponibles)

```json
{
  "id": 88,
  "pickup_address": "Rue des Jardins, Cocody",
  "pickup_name": "La Bonne Table",
  "pickup_lat": 5.3534,
  "pickup_lng": -3.9956,
  "delivery_address": "Riviera 3, Bloc 5, Apt 12",
  "delivery_lat": 5.3612,
  "delivery_lng": -3.9814,
  "distance_to_pickup_km": 1.4,
  "delivery_fee": 15000,
  "driver_earning": 12000,
  "items_count": 3,
  "estimated_minutes": 28,
  "created_at": "2026-07-17T14:00:00Z"
}
```

`driver_earning` = `delivery_fee × 0.80` (commission plateforme 20%).

---

### 7.3 DeliveryDetail (course active)

```json
{
  "id": 88,
  "status": "heading_to_restaurant",
  "status_label": "En route vers le restaurant",
  "pickup": {
    "name": "La Bonne Table",
    "address": "Rue des Jardins, Cocody",
    "phone": "0708000000",
    "lat": 5.3534,
    "lng": -3.9956
  },
  "dropoff": {
    "address": "Riviera 3, Bloc 5, Apt 12",
    "phone": "0701234567",
    "instructions": "Appeler en bas de l'immeuble",
    "lat": 5.3612,
    "lng": -3.9814
  },
  "order": {
    "reference": "CMD-20260717-0088",
    "items_count": 3,
    "total": 87500,
    "delivery_fee": 15000,
    "driver_earning": 12000
  },
  "assigned_at": "2026-07-17T14:02:00Z",
  "picked_up_at": null,
  "delivered_at": null
}
```

---

## 8. Flux de vie d'une livraison

```
┌─────────────────────────────────────────────────────────────────┐
│  CLIENT passe une commande (type: delivery)                     │
│  ORDER créée → status: pending_payment                          │
└────────────────────────────┬────────────────────────────────────┘
                             │ Paiement confirmé
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│  ORDER: status = paid                                           │
│  DELIVERY créée: status = pending                               │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             ▼
┌─────────────────────────────────────────────────────────────────┐
│  DriverAssignmentService::assign()                              │
│                                                                 │
│  Recherche livreur le plus proche :                             │
│  → Rayon 3 km   (tentative 1)                                   │
│  → Rayon 6 km   (tentative 2)                                   │
│  → Rayon 10 km  (tentative 3)                                   │
│                                                                 │
│  Si trouvé  → doAssign() → DELIVERY: status = assigned          │
│               broadcast DriverAssigned (3 canaux)              │
│                                                                 │
│  Si non trouvé → broadcast NewDeliveryAvailable (ville)         │
│               + FCM push à tous les drivers dispo de la ville   │
│               Les drivers voient la course dans pendingOrders   │
│               et peuvent l'accepter manuellement                │
└────────────────────────────┬────────────────────────────────────┘
                             │ Driver accepte / est assigné
                             ▼
            ┌────────────────────────────────────┐
            │  DELIVERY: status = assigned        │
            │  driver.is_available = false        │
            └────────────────┬───────────────────┘
                             │ PATCH /status → heading_to_restaurant
                             ▼
            ┌────────────────────────────────────┐
            │  En route vers le restaurant        │
            └────────────────┬───────────────────┘
                             │ PATCH /status → picked_up
                             ▼
            ┌────────────────────────────────────┐
            │  Commande récupérée                 │
            │  order.picked_up_at = now()         │
            └────────────────┬───────────────────┘
                             │ PATCH /status → delivering
                             ▼
            ┌────────────────────────────────────┐
            │  En livraison                       │
            └────────────────┬───────────────────┘
                             │ PATCH /status → delivered
                             ▼
            ┌────────────────────────────────────┐
            │  DELIVERY: status = delivered       │
            │  ORDER: status = completed          │
            │  delivery.delivered_at = now()      │
            │  driver.is_available = true         │
            │  driver.total_deliveries++          │
            │  DriverEarning créé (net 80%)       │
            └────────────────────────────────────┘
```

---

## 9. Statuts & enums

### 9.1 DeliveryStatus

| Valeur | Label | isActive() |
|--------|-------|-----------|
| `pending` | En attente de livreur | false |
| `assigned` | Livreur assigné | true |
| `heading_to_restaurant` | En route vers le restaurant | true |
| `picked_up` | Commande récupérée | true |
| `delivering` | En livraison | true |
| `delivered` | Livrée | false |
| `cancelled` | Annulée | false |

---

### 9.2 OrderStatus

| Valeur | Label |
|--------|-------|
| `draft` | Brouillon |
| `pending_payment` | En attente de paiement |
| `paid` | Payée |
| `confirmed` | Confirmée |
| `preparing` | En préparation |
| `ready` | Prête |
| `delivering` | En livraison |
| `completed` | Terminée |
| `cancelled` | Annulée |
| `refunded` | Remboursée |

**Transitions autorisées :**
```
draft           → [pending_payment, cancelled]
pending_payment → [paid, cancelled]
paid            → [confirmed, cancelled, refunded]
confirmed       → [preparing, cancelled]
preparing       → [ready]
ready           → [delivering, completed]
delivering      → [completed]
completed       → [refunded]
cancelled       → []
refunded        → []
```

---

### 9.3 OrderType

| Valeur | Label | requiresAddress |
|--------|-------|----------------|
| `dine_in` | Sur place | false |
| `takeaway` | À emporter | false |
| `delivery` | Livraison | **true** |

---

### 9.4 PaymentStatus

| Valeur | Label |
|--------|-------|
| `pending` | En attente |
| `pending_verification` | En attente de vérification |
| `processing` | En cours |
| `completed` | Complété |
| `failed` | Échoué |
| `refunded` | Remboursé |

---

### 9.5 DriverEarning status

| Valeur | Signification |
|--------|--------------|
| `pending` | En cours (livraison pas encore terminée) |
| `available` | Disponible pour virement |
| `paid` | Déjà versé |

---

### 9.6 verification_status (DeliveryDriver)

| Valeur | Comportement |
|--------|-------------|
| `pending` | Peut se connecter, bloqué par middleware sur toutes les routes |
| `approved` | Accès complet |
| `rejected` | Login bloqué (403) |
| `suspended` | Login bloqué (403) |

---

## 10. WebSocket — Canaux & événements temps réel

Implémenté avec Laravel Broadcasting (Pusher/Soketi compatible).

### 10.1 Tableau des canaux

| Canal | Type | Abonné par |
|-------|------|-----------|
| `order.{tracking_token}` | Public | Client qui suit sa commande |
| `private-restaurant.{id}.deliveries` | Privé | Dashboard restaurant |
| `private-driver.{id}` | Privé | App livreur (courses assignées) |
| `private-delivery.{id}` | Privé | Client (position GPS live) |
| `drivers.city.{city}` | Public | App livreur (nouvelles courses dispo) |

---

### 10.2 Événement : `delivery.available`
Canal : `drivers.city.{city}`
Déclenché : quand aucun livreur n'est trouvé automatiquement

```json
{
  "delivery_id": 88,
  "restaurant_name": "La Bonne Table",
  "pickup_address": "Rue des Jardins, Cocody",
  "pickup_lat": 5.3534,
  "pickup_lng": -3.9956,
  "delivery_address": "Riviera 3, Bloc 5, Apt 12",
  "delivery_lat": 5.3612,
  "delivery_lng": -3.9814,
  "delivery_fee": 15000,
  "driver_earning": 12000,
  "items_count": 3,
  "estimated_minutes": 28,
  "city": "Abidjan"
}
```

---

### 10.3 Événement : `driver.assigned`
Canaux : `order.{token}`, `restaurant.{id}.deliveries`, `driver.{id}`

```json
{
  "delivery_id": 88,
  "order_ref": "CMD-20260717-0088",
  "driver": {
    "id": 12,
    "name": "Kouamé Yao",
    "phone": "0701234567",
    "vehicle": "moto",
    "rating": 4.82,
    "lat": 5.3501,
    "lng": -3.9934
  },
  "pickup_address": "Rue des Jardins, Cocody",
  "delivery_address": "Riviera 3, Bloc 5, Apt 12",
  "estimated_minutes": 28,
  "assigned_at": "2026-07-17T14:02:00Z"
}
```

---

### 10.4 Événement : `driver.location`
Canal : `private-delivery.{deliveryId}`
Déclenché : à chaque PATCH `/driver/location` si livraison active

```json
{
  "lat": 5.3512,
  "lng": -3.9945,
  "driver": "Kouamé Yao",
  "status": "heading_to_restaurant"
}
```

---

### 10.5 Événement : `delivery.status_changed`
Canaux : `order.{token}`, `restaurant.{id}.deliveries`, `driver.{id}`

```json
{
  "delivery_id": 88,
  "order_ref": "CMD-20260717-0088",
  "old_status": "assigned",
  "new_status": "heading_to_restaurant",
  "status_label": "En route vers le restaurant",
  "driver": {
    "name": "Kouamé Yao",
    "phone": "0701234567",
    "lat": 5.3512,
    "lng": -3.9945,
    "vehicle": "moto",
    "rating": 4.82
  },
  "estimated_minutes": 22,
  "picked_up_at": null,
  "delivered_at": null
}
```

---

## 11. Notifications push (FCM)

### 11.1 Configuration requise

Utilise l'**API FCM HTTP v1** (OAuth2 JWT — l'ancienne API legacy est désactivée depuis juin 2024).

Variables nécessaires (dans `SystemSetting` ou `config/services.firebase`) :
- `firebase_project_id` — ID du projet Firebase Console
- `firebase_service_account_json` — JSON complet du Service Account (clé privée)

### 11.2 Enregistrement du token FCM

Au démarrage de l'app ou lors du renouvellement FCM :
```
PATCH /api/v1/driver/auth/fcm-token
{ "fcm_token": "fxxxxxxxxxxxxxxxxxxxxx" }
```

### 11.3 Notifications reçues par le livreur

| Trigger | Titre | Corps | `data.type` |
|---------|-------|-------|-------------|
| Nouvelle course disponible | "Nouvelle course disponible 🛵" | "Une commande est disponible dans votre zone. Acceptez-la vite !" | `new_delivery` |

**Data payload :**
```json
{
  "type": "new_delivery",
  "delivery_id": "88",
  "city": "Abidjan"
}
```

### 11.4 Notifications reçues par le client

| Statut commande | Titre | Corps |
|----------------|-------|-------|
| `confirmed` | "Commande confirmée ✅" | "Votre commande #X est en préparation chez {restaurant}." |
| `preparing` | "En préparation 👨‍🍳" | "{restaurant} prépare votre commande #X." |
| `ready` | "Commande prête 📦" | "...un livreur va la récupérer." |
| `picked_up` | "Livreur en route 🛵" | "Votre commande #X est en chemin !" |
| `delivered` | "Livraison effectuée 🎉" | "Votre commande #X a été livrée. Bon appétit !" |
| `cancelled` | "Commande annulée ❌" | "Votre commande #X a été annulée." |

**Data payload :**
```json
{
  "type": "order_status",
  "order_id": "42",
  "status": "delivered"
}
```

---

## 12. Modèle de données

### 12.1 Table `delivery_drivers`

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | bigint PK | |
| `user_id` | FK → users | nullable |
| `name` | varchar(100) | |
| `phone` | varchar(20) | unique |
| `email` | varchar(150) | nullable |
| `city` | varchar(100) | utilisé pour matcher les courses |
| `zone` | varchar(100) | nullable, quartier |
| `vehicle_type` | varchar(50) | `moto` \| `vélo` \| `voiture` |
| `vehicle_plate` | varchar(20) | nullable |
| `cni_number` | varchar(30) | |
| `cni_photo_path` | string | disk: public |
| `license_photo_path` | string | disk: public |
| `vehicle_photo_path` | string | disk: public |
| `verification_status` | varchar(20) | `pending` \| `approved` \| `rejected` \| `suspended` |
| `is_active` | boolean | |
| `is_available` | boolean | en ligne et disponible |
| `latitude` | decimal(10,7) | position actuelle |
| `longitude` | decimal(10,7) | position actuelle |
| `location_updated_at` | timestamp | |
| `total_deliveries` | uint | compteur lifetime |
| `rating` | decimal(3,2) | note moyenne (défaut: 5.00) |
| `total_ratings` | uint | nb d'évaluations |
| `total_cancelled` | uint | courses déclinées/annulées |
| `total_earnings_xof` | ubigint | gains nets lifetime en centimes |
| `fcm_token` | string | pour push notifications |
| `token` | varchar(64) | token legacy (non utilisé) |

Index : `[verification_status]`, `[city, is_available, is_active]`

---

### 12.2 Table `deliveries`

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | bigint PK | |
| `order_id` | FK → orders | cascade delete |
| `restaurant_id` | FK → restaurants | cascade delete |
| `driver_id` | FK → delivery_drivers | nullable, null on delete |
| `status` | varchar(30) | DeliveryStatus enum |
| `delivery_address` | text | |
| `delivery_phone` | varchar(20) | |
| `delivery_instructions` | text | nullable |
| `pickup_latitude` | decimal(10,7) | coords restaurant |
| `pickup_longitude` | decimal(10,7) | |
| `delivery_latitude` | decimal(10,7) | coords client |
| `delivery_longitude` | decimal(10,7) | |
| `driver_latitude` | decimal(10,7) | position driver live |
| `driver_longitude` | decimal(10,7) | |
| `driver_location_at` | timestamp | |
| `assigned_at` | timestamp | nullable |
| `picked_up_at` | timestamp | nullable |
| `delivered_at` | timestamp | nullable |
| `cancelled_at` | timestamp | nullable |
| `cancellation_reason` | text | nullable |
| `estimated_minutes` | uint | |

Index : `[restaurant_id, status]`, `[driver_id, status]`

---

### 12.3 Table `driver_earnings`

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | bigint PK | |
| `driver_id` | FK → delivery_drivers | cascade |
| `order_id` | FK → orders | cascade |
| `delivery_id` | FK → deliveries | cascade |
| `gross_amount` | uint | = delivery_fee de la commande |
| `platform_cut` | uint | 20% de gross_amount |
| `net_amount` | uint | 80% de gross_amount |
| `status` | varchar(20) | `pending` \| `available` \| `paid` |
| `paid_at` | timestamp | nullable |
| `payment_method` | varchar(30) | `wave` \| `orange_money` \| `mtn_money` |
| `payment_reference` | varchar(100) | nullable, ref Wave |

Index : `[driver_id, status]`, `[driver_id, created_at]`

---

### 12.4 Table `driver_locations` (historique GPS)

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | bigint PK | |
| `driver_id` | FK → delivery_drivers | cascade |
| `latitude` | decimal(10,7) | |
| `longitude` | decimal(10,7) | |
| `accuracy` | decimal(6,2) | mètres |
| `speed` | decimal(6,2) | km/h |
| `heading` | decimal(5,2) | degrés 0-360 |
| `recorded_at` | timestamp | |

Pas de `created_at`/`updated_at`. Index : `[driver_id, recorded_at]`

---

### 12.5 Table `delivery_cities`

| Colonne | Type | Default | Notes |
|---------|------|---------|-------|
| `id` | bigint PK | | |
| `name` | varchar(100) | | Ex: "Abidjan" |
| `slug` | varchar(100) unique | | Ex: "abidjan" |
| `country` | varchar(5) | `'CI'` | |
| `center_latitude` | decimal(10,7) | | Centre géographique |
| `center_longitude` | decimal(10,7) | | |
| `coverage_radius_km` | uint | `15` | Rayon de couverture plateforme |
| `is_active` | boolean | | |
| `delivery_base_fee` | uint | `50000` | 500 FCFA en centimes |
| `delivery_fee_per_km` | uint | `15000` | 150 FCFA/km en centimes |
| `peak_hour_surcharge_percent` | uint | `20` | +20% aux heures de pointe |
| `max_delivery_distance_km` | uint | `10` | Distance max client ↔ restaurant |
| `min_order_amount` | uint | `0` | Montant minimum commande |
| `currency` | varchar(5) | `'XOF'` | |

**Heures de pointe :** 11h–14h et 18h–21h

---

### 12.6 Table `delivery_zones`

| Colonne | Type | Notes |
|---------|------|-------|
| `id` | bigint PK | |
| `delivery_city_id` | FK → delivery_cities | nullable |
| `name` | varchar | Ex: "Cocody" |
| `city` | varchar | |
| `country` | varchar(5) | default: 'CI' |
| `center_latitude` | decimal(10,7) | |
| `center_longitude` | decimal(10,7) | |
| `radius_km` | uint | default: 5 |
| `is_active` | boolean | |
| `sort_order` | uint | default: 0 |

---

### 12.7 Champs livraison dans `orders`

| Colonne | Type | Notes |
|---------|------|-------|
| `type` | enum | `dine_in` \| `takeaway` \| `delivery` |
| `status` | enum | OrderStatus |
| `delivery_fee` | uint | en centimes |
| `delivery_address` | text | nullable |
| `delivery_city` | string | nullable |
| `delivery_latitude` | decimal(10,8) | nullable |
| `delivery_longitude` | decimal(11,8) | nullable |
| `delivery_instructions` | text | nullable |
| `source` | varchar(20) | `pos` \| `platform_web` \| `platform_app` |
| `platform_commission` | uint | centimes, commission plateforme |
| `driver_assigned_at` | timestamp | nullable |
| `picked_up_at` | timestamp | nullable |

---

## 13. Calcul des tarifs & commission

### 13.1 Frais de livraison (côté client)

```
fee = base_fee + (distance_km × fee_per_km)

Si heure de pointe (11h-14h ou 18h-21h) :
fee = fee × (1 + peak_surcharge_percent / 100)
```

**Exemple avec les valeurs par défaut Abidjan :**
```
distance = 4 km
base_fee = 500 FCFA (50000 centimes)
fee/km   = 150 FCFA (15000 centimes)

fee = 500 + (4 × 150) = 500 + 600 = 1 100 FCFA

Si heure de pointe (surcharge 20%) :
fee = 1 100 × 1.20 = 1 320 FCFA
```

### 13.2 Revenus livreur

```
driver_earning = delivery_fee × 0.80   (80%)
platform_cut   = delivery_fee × 0.20   (20%)
```

**Exemple :**
```
delivery_fee = 1 100 FCFA
driver_earning = 880 FCFA
platform_cut   = 220 FCFA
```

### 13.3 Distance maximale

La commande est refusée (côté livraison) si la distance restaurant ↔ client dépasse `max_delivery_distance_km` (défaut : 10 km, Abidjan : 25 km).

---

## 14. Rate limits

| Endpoint | Limite | Clé |
|----------|--------|-----|
| `POST /driver/auth/login` | 5/min par IP + 5/15min par compte | IP + phone |
| `POST /driver/auth/register` | 5/heure | IP |
| `PATCH /driver/location` | **30/min** | `driver:{user_id}` |
| `POST /driver/earnings/payout` | **3/jour** | `payout:{user_id}` |
| `GET /ticker` | 120/min | IP |
| `POST /ticker/{id}/dismiss` | 60/min | user |

Toutes les réponses de rate limit retournent HTTP **429** en JSON.

---

## 15. Headers & middleware globaux

### Headers à inclure dans chaque requête

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {token}      (routes protégées)
```

### Réponses d'erreur standard

```json
// 401 — Non authentifié
{ "message": "Unauthenticated." }

// 403 — Accès refusé
{ "message": "Accès réservé aux livreurs." }

// 403 — Compte en vérification
{
  "message": "Votre compte est en cours de vérification par notre équipe.",
  "verification_status": "pending"
}

// 422 — Validation
{
  "message": "The given data was invalid.",
  "errors": {
    "phone": ["Le champ phone est obligatoire."]
  }
}

// 429 — Rate limit
{
  "message": "Too Many Requests.",
  "retry_after": 60
}
```

---

## 16. Schéma base de données

```
users
  id, name, email, password, role (delivery_driver), ...

delivery_drivers
  id, user_id→users, name, phone, email
  city, zone, vehicle_type, vehicle_plate
  cni_number, cni_photo_path, license_photo_path, vehicle_photo_path
  verification_status, is_active, is_available
  latitude, longitude, location_updated_at
  total_deliveries, rating, total_ratings, total_cancelled
  total_earnings_xof, fcm_token

driver_locations
  id, driver_id→delivery_drivers
  latitude, longitude, accuracy, speed, heading, recorded_at

deliveries
  id, order_id→orders, restaurant_id→restaurants
  driver_id→delivery_drivers (nullable)
  status, delivery_address, delivery_phone, delivery_instructions
  pickup_latitude, pickup_longitude
  delivery_latitude, delivery_longitude
  driver_latitude, driver_longitude, driver_location_at
  assigned_at, picked_up_at, delivered_at, cancelled_at
  cancellation_reason, estimated_minutes

driver_earnings
  id, driver_id→delivery_drivers
  order_id→orders, delivery_id→deliveries
  gross_amount, platform_cut, net_amount
  status, paid_at, payment_method, payment_reference

delivery_cities
  id, name, slug, country
  center_latitude, center_longitude, coverage_radius_km
  is_active, delivery_base_fee, delivery_fee_per_km
  peak_hour_surcharge_percent, max_delivery_distance_km
  min_order_amount, currency

delivery_zones
  id, delivery_city_id→delivery_cities
  name, city, country, center_latitude, center_longitude
  radius_km, is_active, sort_order

orders (champs livraison)
  type (delivery|dine_in|takeaway)
  status (OrderStatus)
  delivery_fee, delivery_address, delivery_city
  delivery_latitude, delivery_longitude, delivery_instructions
  source, platform_commission, driver_assigned_at, picked_up_at

announcements (ticker)
  id, title, content, type, target
  link_url, link_label, show_on_ticker, show_on_dashboard
  is_active, is_dismissible, starts_at, ends_at

announcement_dismissals
  id, announcement_id→announcements, user_id→users
```

---

*Ce document est généré depuis le code source de MenuPro — branche `main` — commit `c6abc81`.*

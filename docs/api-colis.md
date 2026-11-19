# API Colis — contrat

Livraison de colis MenuPro : un expéditeur (client de l'app, commerçant inscrit ou partenaire
API) demande une course ; un livreur MenuPro ramasse le colis (photo obligatoire) et le remet au
destinataire contre un **code à 4 chiffres**. Pas de contre-remboursement : le livreur n'encaisse
jamais la valeur de l'article (colonne `cod_amount` réservée à une phase 2, toujours `null`).

- Base : `https://<domaine>/api/v1`
- En-têtes : `Accept: application/json` (toutes les réponses sont en JSON)
- Montants : FCFA entiers (`XOF`). Dates : ISO 8601.
- Référence de colis : `COL-XXXXXX` (6 caractères, sans 0/O/1/I/L).

## Sommaire

1. [Tarifs](#1-tarifs)
2. [Statuts et machine d'états](#2-statuts)
3. [Erreurs](#3-erreurs)
4. [Objet colis (expéditeur)](#4-objet-colis-expéditeur)
5. [API client (app MPA)](#5-api-client)
6. [Suivi public (destinataire)](#6-suivi-public)
7. [API livreur (app MPADriver)](#7-api-livreur)
8. [API partenaires](#8-api-partenaires)
9. [Webhooks partenaires](#9-webhooks-partenaires)
10. [Notifications push](#10-notifications-push)

---

## 1. Tarifs

```
distance route = Haversine(ramassage, remise) × 1,3          (arrondie à 0,01 km)
course         = base + prix_km × distance route, arrondie au multiple de 50 F supérieur
prix           = course + supplément taille
part livreur   = prix × 80 %            commission MenuPro = prix − part livreur
```

| Paramètre                 | Défaut | Réglable par ville (admin) |
|---------------------------|--------|-----------------------------|
| Base                      | 500 F  | oui |
| Prix / km                 | 100 F  | oui |
| Supplément `small`        | 0 F    | oui |
| Supplément `medium`       | 200 F  | oui |
| Supplément `large`        | 500 F  | oui |
| Plafond de distance route | 15 km  | oui (au-delà : refus `distance_too_long`) |
| Part livreur              | 80 %   | oui (même règle que les repas) |

La ville est détectée à partir du **point de ramassage** (villes de livraison actives). Exemple :
3 km à vol d'oiseau → 3,9 km route → 500 + 390 = 890 → **900 F** (petit), 1 100 F (moyen), 1 400 F (grand).

Tailles : `small` (enveloppe, sachet), `medium` (carton à chaussures), `large` (carton, sac de voyage).

## 2. Statuts

| Statut             | Sens |
|--------------------|------|
| `pending_payment`  | Colis Jeko créé, paiement attendu (annulé automatiquement après 60 min) |
| `searching_driver` | Recherche d'un livreur (espèces et partenaires : dès la création ; Jeko : après paiement) |
| `assigned`         | Livreur trouvé, en route vers le ramassage |
| `picked_up`        | Colis ramassé (photo prise ; espèces encaissées) |
| `delivering`       | En route vers le destinataire |
| `delivered`        | Remis avec le bon code |
| `cancelled`        | Annulé (expéditeur avant ramassage, admin, ou système) |
| `failed`           | Échec après ramassage (le colis doit être rendu ; MenuPro est alerté) |

Transitions autorisées :

```
pending_payment  → searching_driver | cancelled
searching_driver → assigned | cancelled
assigned         → picked_up | searching_driver (désistement livreur) | cancelled
picked_up        → delivering | failed | cancelled (admin)
delivering       → delivered | failed | cancelled (admin)
delivered, cancelled, failed : terminaux
```

Statuts de paiement (`payment.status`) : `pending`, `paid`, `failed` (Jeko refusé, on peut relancer),
`refunded`, `billed_to_partner`. `payment.refund_status` : `null`, `pending` (remboursement à faire
par MenuPro), `refunded`.

Modes de paiement (`payment.method`) : `jeko` (Mobile Money), `cash` (espèces remises au livreur
**au ramassage**), `partner_billing` (partenaires uniquement).

## 3. Erreurs

Format d'une erreur métier :

```json
{ "message": "Distance trop longue : 15,6 km par la route, le maximum à Daloa est de 15 km.",
  "error_code": "distance_too_long", "distance_km": 15.6, "max_distance_km": 15 }
```

Erreur de validation (HTTP 422, format Laravel, messages en français) :

```json
{ "message": "Le numéro de téléphone « téléphone du destinataire » n'est pas valide.",
  "errors": { "recipient_phone": ["Le numéro de téléphone « téléphone du destinataire » n'est pas valide."] } }
```

| HTTP | `error_code` | Quand |
|------|--------------|-------|
| 401 | `invalid_api_key` | Partenaire : clé absente/invalide |
| 403 | `partner_disabled` | Partenaire désactivé |
| 403 | `no_customer_profile` | Compte sans profil client |
| 403 | `not_your_parcel` | Livreur non assigné à ce colis |
| 403 | `wrong_city` | Livreur d'une autre ville |
| 404 | `not_found` | Colis inexistant **ou** appartenant à un autre (pas de fuite d'existence) |
| 409 | `already_taken` | Colis déjà accepté par un autre livreur |
| 422 | `city_not_served` | Ramassage hors des villes desservies |
| 422 | `city_disabled` | Service colis suspendu dans la ville |
| 422 | `city_not_allowed_for_partner` | Ville non ouverte au partenaire |
| 422 | `distance_too_long` | Au-delà du plafond |
| 422 | `sender_phone_required` | Client sans téléphone et sans `sender_phone` |
| 422 | `cannot_cancel` | Déjà ramassé / terminé |
| 422 | `invalid_transition` | Action impossible dans l'état actuel (`current_status` fourni) |
| 422 | `cash_not_confirmed` | Ramassage espèces sans `cash_received=1` |
| 422 | `invalid_code` | Mauvais code (`attempts_left` fourni) |
| 422 | `driver_offline` / `driver_busy` | Livreur hors ligne / déjà en course (repas ou colis) |
| 422 | `payment_not_expected` | `pay` sur un colis non Jeko ou déjà payé |
| 423 | `code_locked` | 5 essais ratés : code bloqué, admin alerté |
| 429 | `rate_limited` | Trop de requêtes |
| 502 / 503 | `payment_init_failed` / `payment_unavailable` | Jeko injoignable / non configuré |

## 4. Objet colis (expéditeur)

Renvoyé par l'API client (et, sans `handover_code`, par l'API partenaire) :

```json
{
  "reference": "COL-K7TQ4B",
  "status": "assigned",
  "status_label": "Livreur en route vers le ramassage",
  "is_final": false,
  "city": "Daloa",
  "size": "medium",
  "size_label": "Moyen (carton à chaussures)",
  "description": "Deux pagnes",
  "note": "Sonner au portail bleu",
  "pickup":  { "address": "Marché central", "latitude": 6.8774, "longitude": -6.4502 },
  "dropoff": { "address": "Quartier Tazibouo", "latitude": 6.9044, "longitude": -6.4502 },
  "sender":    { "name": "Awa Koné", "phone": "+225 0701020304" },
  "recipient": { "name": "Fatou Bamba", "phone": "+225 0505050505" },
  "distance_km": 3.9,
  "price": 1100,
  "payment": { "method": "cash", "status": "pending", "paid_at": null, "refund_status": null },
  "tracking_token": "Zx8…(48 caractères)",
  "tracking_url": "https://app.menupro.ci/?parcel=Zx8…",
  "pickup_photo_url": null,
  "eta_minutes": 14,
  "steps": [
    { "key": "created", "label": "Demande créée", "done": true, "current": false, "at": "2026-11-19T10:02:11+00:00" },
    { "key": "searching_driver", "label": "Recherche d'un livreur", "done": true, "current": false, "at": "…" },
    { "key": "assigned", "label": "Livreur trouvé", "done": true, "current": true, "at": "…" },
    { "key": "picked_up", "label": "Colis ramassé", "done": false, "current": false, "at": null },
    { "key": "delivering", "label": "En route vers le destinataire", "done": false, "current": false, "at": null },
    { "key": "delivered", "label": "Livré", "done": false, "current": false, "at": null }
  ],
  "cancellation_reason": null,
  "failure_reason": null,
  "created_at": "2026-11-19T10:02:11+00:00",
  "handover_code": "4821",
  "driver": {
    "first_name": "Koffi", "name": "Koffi Yao", "phone": "+225 0712345678",
    "vehicle": "moto", "photo_url": null,
    "latitude": 6.8801, "longitude": -6.4490, "location_updated_at": "…"
  },
  "can_cancel": true
}
```

- `handover_code` : **à transmettre au destinataire** (WhatsApp/SMS non branchés : l'app propose
  de partager `tracking_url` + le code). `null` une fois le colis terminé.
- `driver.phone`, `latitude`, `longitude` : uniquement pendant la course active (assigned →
  delivering). Rafraîchir toutes les 10–15 s (même mécanisme que le suivi de commande).
- `eta_minutes` : estimation (moto 25 km/h), `null` si inconnue.

## 5. API client

Auth : `Authorization: Bearer <token Sanctum client>` (mêmes jetons que `/client/orders`).

### POST `/client/parcels/quote`

```json
{ "pickup_lat": 6.8774, "pickup_lng": -6.4502, "dropoff_lat": 6.9044, "dropoff_lng": -6.4502, "size": "medium" }
```

200 :

```json
{ "city": "Daloa", "distance_km": 3.9, "price": 1100, "currency": "XOF", "size": "medium",
  "breakdown": { "base_fee": 500, "distance_fee": 390, "course_rounded": 900, "size_supplement": 200 },
  "payment_methods": ["jeko", "cash"] }
```

### POST `/client/parcels` (30 créations/heure/compte)

| Champ | Règle |
|-------|-------|
| `pickup_address`, `dropoff_address` | requis, 3–300 car. |
| `pickup_lat`, `pickup_lng`, `dropoff_lat`, `dropoff_lng` | requis, numériques |
| `size` | `small` \| `medium` \| `large` |
| `recipient_name` | requis, 2–120 |
| `recipient_phone` | requis, téléphone (`+225 07…`, chiffres, espaces, points, tirets) |
| `description` | requis, 2–160 |
| `note` | facultatif, ≤ 300 |
| `sender_name`, `sender_phone` | facultatifs (défaut : nom du compte, téléphone du profil client) |
| `payment_method` | `jeko` \| `cash` |
| `jeko_operator` | facultatif : `wave` (défaut) \| `orange` \| `mtn` \| `moov` |

201, espèces :

```json
{ "parcel": { …objet colis, "status": "searching_driver" }, "next_step": "track" }
```

201, Jeko :

```json
{ "parcel": { …, "status": "pending_payment" }, "next_step": "payment",
  "payment": { "payment_url": "https://pay.jeko.africa/…", "payment_id": "jeko-link-123" } }
```

Si Jeko est injoignable, le colis est quand même créé : `"payment": null` et
`"payment_error": { "message": "…", "error_code": "payment_init_failed" }` → relancer `pay`.
Après paiement, Jeko redirige vers `FRONTEND_URL/?parcel=<tracking_token>` (ou
`…&payment_error=1`). **Le statut fait foi via le webhook signé** : relire le colis.

### POST `/client/parcels/{ref}/pay`

Relance le paiement Jeko d'un colis `pending_payment`. 200 : `{ "payment": { "payment_url": "…", "payment_id": "…" } }`.

### GET `/client/parcels?status=active|done`

```json
{ "data": [ …objets colis ], "meta": { "current_page": 1, "last_page": 1, "total": 3 } }
```

### GET `/client/parcels/{ref}`

200 : `{ "parcel": { … } }` — 404 si le colis n'est pas au client.

### POST `/client/parcels/{ref}/cancel`

Corps facultatif `{ "reason": "Plus besoin" }`. Possible seulement avant ramassage
(`pending_payment`, `searching_driver`, `assigned`), sinon 422 `cannot_cancel`.

```json
{ "message": "Colis annulé. Votre paiement vous sera remboursé par MenuPro.", "parcel": { … "payment": { "refund_status": "pending" } } }
```

Payé par Jeko → `refund_status: "pending"`, l'admin est alerté et rembourse manuellement (même
mécanisme que les commandes refusées). Le livreur éventuel est libéré et prévenu.

## 6. Suivi public

### GET `/parcels/track/{token}` (sans compte)

Pour le destinataire. **Aucun téléphone, aucune adresse, aucun code.**

```json
{ "parcel": {
  "reference": "COL-K7TQ4B", "status": "delivering", "status_label": "En cours de livraison",
  "is_final": false, "city": "Daloa", "size_label": "Moyen (carton à chaussures)",
  "recipient_first_name": "Fatou",
  "driver": { "first_name": "Koffi", "vehicle": "moto" },
  "eta_minutes": 9,
  "steps": [ … ]
} }
```

404 `not_found` si le jeton est inconnu. Lien à partager : `tracking_url` de l'objet colis.

## 7. API livreur

Auth et conventions identiques aux routes `/driver/deliveries/*` (`auth:sanctum` + profil livreur
approuvé). Chaque élément porte `"kind": "parcel"` pour cohabiter avec les courses repas.
**Un livreur ne mène qu'une course à la fois** : un colis en cours bloque l'acceptation d'un repas
et inversement.

Objet colis côté livreur :

```json
{
  "kind": "parcel", "reference": "COL-K7TQ4B", "status": "assigned", "status_label": "…",
  "city": "Daloa", "size": "medium", "size_label": "…", "description": "Deux pagnes", "note": null,
  "pickup": { "address": "Marché central", "latitude": 6.8774, "longitude": -6.4502,
              "contact_name": "Awa Koné", "contact_phone": "+225 0701020304" },
  "dropoff": { "address": "Quartier Tazibouo", "latitude": 6.9044, "longitude": -6.4502,
               "recipient_name": "Fatou Bamba", "recipient_phone": "+225 0505050505" },
  "distance_km": 3.9, "distance_to_pickup_km": 0.4,
  "price": 1100, "driver_earning_estimate": 880,
  "payment_method": "cash", "cash_to_collect": 1100, "cash_collected": false,
  "handover_attempts_left": 5, "handover_locked": false,
  "pickup_photo_url": null,
  "assigned_at": "…", "picked_up_at": null, "delivering_at": null, "delivered_at": null, "created_at": "…"
}
```

Avant acceptation (`available`), les contacts sont masqués (`contact_*`, `recipient_phone` = `null`,
`recipient_name` = prénom). Le code de remise n'est **jamais** envoyé au livreur.

| Méthode | Route | Corps | Effet |
|---------|-------|-------|-------|
| GET | `/driver/parcels/available` | — | Colis `searching_driver` de la ville du livreur, triés par distance (vide si hors ligne) |
| GET | `/driver/parcels/active` | — | `{ "data": colis \| null }` |
| GET | `/driver/parcels/history` | — | Colis livrés/échoués/annulés du livreur (paginé) |
| GET | `/driver/parcels/{ref}` | — | Détail (403 si pas le sien) |
| POST | `/driver/parcels/{ref}/accept` | — | → `assigned` (verrou : 409 `already_taken` pour le second) |
| POST | `/driver/parcels/{ref}/pickup` | multipart : `photo` (requis, jpeg/png/webp, ≤ 6 Mo), `cash_received` (`1` requis si `payment_method = cash`) | → `picked_up` |
| POST | `/driver/parcels/{ref}/start` | — | → `delivering` |
| POST | `/driver/parcels/{ref}/deliver` | `{ "code": "4821" }` (4 chiffres) | → `delivered` |
| POST | `/driver/parcels/{ref}/fail` | `{ "reason": "Destinataire injoignable" }` (3–500) | voir ci-dessous |

Réponses : `{ "message": "…", "data": { colis } }`. `deliver` renvoie aussi `"earning": 880`.

**Code de remise** : mauvais code → 422 `invalid_code` + `attempts_left` ; au 5e échec → 423
`code_locked` (admin alerté, même le bon code est refusé jusqu'au déblocage par l'admin).

**`fail`** : avant ramassage (`assigned`) = désistement, le colis repart en recherche
(`{ "status": "searching_driver" }`) ; après ramassage = échec (`{ "status": "failed" }`), le livreur
redevient disponible, l'admin est alerté pour organiser le retour du colis.

**Espèces** : au ramassage le livreur encaisse `cash_to_collect` auprès de l'expéditeur, garde sa
part et doit la commission à MenuPro. La dette apparaît dans `GET /driver/cash-balance`
(`restaurant_name: "MenuPro"`, `order_ref: "COL-…"`, `kind: "parcel"`) et se déclare comme les autres
via `POST /driver/cash-remittances` ; elle est soldée par l'admin (fiche colis → « Commission reçue »).
Le gain d'un colis espèces est enregistré `status: "paid"` (déjà en main, non retirable) ; Jeko et
partenaires : `available` (retirable comme un repas). `GET /driver/earnings/history` porte
`kind: "parcel"` et `order_ref` = référence du colis.

## 8. API partenaires

Pour les plateformes externes (ex. vendeuses en ligne) qui créent des colis pour leurs clientes.

- Auth : `Authorization: Bearer mpk_…` (clé fournie une seule fois par MenuPro, régénérable).
- Limite : par partenaire (défaut 60 requêtes/min) → 429 `rate_limited`.
- Paiement : **facturé au partenaire** (`payment.method = partner_billing`,
  `payment.status = billed_to_partner`) ; récapitulatif mensuel côté MenuPro. Remise ou grille propre
  possibles par contrat.
- Idempotence : `external_reference` (votre numéro de commande) est **unique par partenaire**.
  Recréer avec la même référence renvoie le colis existant (HTTP 200, `idempotent_replay: true`)
  au lieu d'en créer un second.

| Méthode | Route |
|---------|-------|
| POST | `/partner/parcels/quote` (mêmes champs que le devis client) |
| POST | `/partner/parcels` |
| GET  | `/partner/parcels/{ref}` (`{ref}` = `COL-…` ou votre `external_reference`) |
| POST | `/partner/parcels/{ref}/cancel` (avant ramassage) |

Création — champs du client (sans `payment_method`) + :

| Champ | Règle |
|-------|-------|
| `external_reference` | requis, ≤ 100 |
| `sender_name`, `sender_phone` | requis (votre boutique / service client) |
| `pickup_contact.name`, `pickup_contact.phone` | facultatifs : la vendeuse chez qui ramasser |

```bash
curl -X POST https://www.menupro.ci/api/v1/partner/parcels/quote \
  -H "Authorization: Bearer mpk_xxx" -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"pickup_lat":6.8774,"pickup_lng":-6.4502,"dropoff_lat":6.9044,"dropoff_lng":-6.4502,"size":"small"}'
# → {"city":"Daloa","distance_km":3.9,"price":900,"currency":"XOF","size":"small","partner_discount":0}

curl -X POST https://www.menupro.ci/api/v1/partner/parcels \
  -H "Authorization: Bearer mpk_xxx" -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{
    "external_reference": "CMD-1001",
    "sender_name": "Vendeuses CI", "sender_phone": "+225 0102030405",
    "pickup_contact": {"name": "Mariam", "phone": "+225 0708091011"},
    "pickup_address": "Marché central, Daloa", "pickup_lat": 6.8774, "pickup_lng": -6.4502,
    "dropoff_address": "Quartier Tazibouo", "dropoff_lat": 6.9044, "dropoff_lng": -6.4502,
    "recipient_name": "Fatou Bamba", "recipient_phone": "+225 0505050505",
    "size": "small", "description": "Robe wax"
  }'
```

201 (200 si rejeu idempotent) :

```json
{ "parcel": { …objet colis, "external_reference": "CMD-1001",
              "pickup_contact": { "name": "Mariam", "phone": "+225 0708091011" },
              "payment": { "method": "partner_billing", "status": "billed_to_partner", … },
              "cod_amount": null, "handover_code": "4821" },
  "idempotent_replay": false }
```

Transmettez `handover_code` et `tracking_url` à votre cliente. `GET /partner/parcels/{ref}` ne
renvoie pas le code (il est renvoyé par un rejeu de création).

```bash
curl https://www.menupro.ci/api/v1/partner/parcels/CMD-1001 -H "Authorization: Bearer mpk_xxx" -H "Accept: application/json"
curl -X POST https://www.menupro.ci/api/v1/partner/parcels/CMD-1001/cancel \
  -H "Authorization: Bearer mpk_xxx" -H "Accept: application/json" -d '{"reason":"Commande annulée"}' -H "Content-Type: application/json"
```

## 9. Webhooks partenaires

À chaque changement de statut, MenuPro envoie un `POST` JSON à l'URL déclarée (https) :

```
POST https://partenaire.example/hooks/menupro
Content-Type: application/json
X-MenuPro-Event: parcel.status_changed
X-MenuPro-Signature: 5f2c…   (HMAC-SHA256 hexadécimal du corps brut, clé = secret de signature)
X-MenuPro-Attempt: 1
User-Agent: MenuPro-Webhooks/1.0

{ "event": "parcel.status_changed", "sent_at": "2026-11-19T10:05:00+00:00",
  "data": { …objet colis partenaire (sans handover_code) } }
```

Événements : `parcel.status_changed` (création incluse, statut dans `data.status`) et `ping`
(bouton « Tester le webhook » de l'admin).

**Vérification** (calculer sur le corps **brut**, avant tout décodage JSON, comparaison à temps constant) :

```php
$body = file_get_contents('php://input');
$expected = hash_hmac('sha256', $body, getenv('MENUPRO_WEBHOOK_SECRET'));
if (!hash_equals($expected, $_SERVER['HTTP_X_MENUPRO_SIGNATURE'] ?? '')) { http_response_code(401); exit; }
```

```js
// Node / Express : app.post('/hooks/menupro', express.raw({ type: 'application/json' }), handler)
const crypto = require('crypto');
const expected = crypto.createHmac('sha256', process.env.MENUPRO_WEBHOOK_SECRET).update(req.body).digest('hex');
const ok = crypto.timingSafeEqual(Buffer.from(expected), Buffer.from(req.get('X-MenuPro-Signature') || ''.padEnd(64)));
```

Répondre **2xx** rapidement. Sinon (non-2xx, délai > 10 s, erreur réseau), nouvel essai après
30 s, 2 min, 10 min, 30 min, 1 h (6 tentatives au total), puis abandon. Les envois peuvent arriver
en double ou dans le désordre : se fier à `data.status` et à `sent_at`, traiter de façon idempotente.
Chaque tentative est visible dans la fiche partenaire (admin).

## 10. Notifications push

Canal push existant (FCM, `data.badge` ajouté automatiquement).

| Destinataire | `data.type` | Quand |
|--------------|-------------|-------|
| Expéditeur (client app / personnel du commerce) | `parcel_driver_found`, `parcel_picked_up`, `parcel_delivering`, `parcel_delivered`, `parcel_cancelled`, `parcel_failed` | à chaque étape (`data.parcel_reference`, `data.status`, tag `parcel-{id}`) |
| Livreurs proches | `new_parcel` (`kind: "parcel"`) | nouvelle course, puis relances toutes les 2 min (rayons 3 → 6 → 10 km) |
| Livreur assigné | `parcel_cancelled` | annulation par l'expéditeur/l'admin |
| Super-admins | `parcel_no_driver`, `parcel_code_locked`, `parcel_failed`, `parcel_refund_required` | aucun livreur après 10 min, code bloqué, échec, remboursement à faire |

Lien d'ouverture de l'app client : `CLIENT_APP_URL/?open=parcels&ref=COL-…` ; suivi public :
`CLIENT_APP_URL/?parcel=<tracking_token>` ; app livreur : `DRIVER_APP_URL/?open=parcels`.

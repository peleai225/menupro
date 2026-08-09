# Système de Livraison Dual-Mode — Design Spec

## Vue d'ensemble

MenuPro gère deux modes de livraison selon l'origine de la commande :

| Mode | Source commande | Livreurs | Gestion |
|------|----------------|----------|---------|
| **Mode Restaurant** | Lien restaurant, QR code (platform_web / pos) | Livreurs propres du restaurant | Restaurant gère manuellement |
| **Mode Plateforme** | App MPA (platform_app) | Livreurs MenuPro | Système automatique |

---

## Architecture globale

```
Order.source = 'platform_web' / 'pos'
  → Mode Restaurant
  → Aucun livreur MenuPro
  → Restaurant confirme livraison depuis son backoffice

Order.source = 'platform_app'
  → Mode Plateforme
  → DriverAssignmentService assigne un livreur MenuPro
  → Flux financier complet (Jeko ou cash)
```

---

## Composant 1 — Mode Restaurant (platform_web/pos)

### Ce qui existe
Le restaurant reçoit la commande. La livraison est gérée par lui.

### Ce qui manque
Dans le backoffice restaurant (page Orders), quand une commande est en type `delivery` :
- Bouton **"Marquer comme livré"** → passe commande en `COMPLETED`
- Si `payment_method = cash_on_delivery` → bouton **"Argent reçu en main"** → marque `payment_status = completed`, `payout_status = manual`

### Flux
```
Restaurant confirme livraison → Order.status = COMPLETED
  Si cash_on_delivery :
    → Order.payment_status = completed
    → Order.payout_status = manual
    → Log "Argent reçu cash par le restaurant directement"
  Si paiement en ligne :
    → Déjà géré par Jeko/Wave webhook
```

### Règle de routage
```php
// Dans OrderController / Orders Livewire
if ($order->type === OrderType::DELIVERY && $order->source !== 'platform_app') {
    // Mode Restaurant — pas de delivery record créé
    // Le restaurant confirme manuellement
}
```

---

## Composant 2 — Mode Plateforme (platform_app)

### Flux paiement en ligne (Wave/Jeko)

```
1. Client paie via Wave → Jeko webhook reçu
2. Order.payment_status = completed
3. ProcessJekoPayoutJob (délai 10min) :
   → Reverser au restaurant : total - platform_commission - delivery_fee
   → Order.payout_status = completed
4. À DELIVERED :
   → creditDriverEarning() : gross = delivery_fee, net = 80%
   → DriverEarning.status = 'available'
5. Livreur demande payout Wave → reçoit ses gains
```

### Flux paiement cash (cash_on_delivery) — À CONSTRUIRE

```
1. Livreur arrive chez le client
2. Client paie X F cash au livreur
3. Livreur confirme via app : "J'ai collecté X F"
   → Endpoint POST /driver/deliveries/{id}/cash-collected
   → Delivery.cash_collected = true
   → Delivery.cash_collected_amount_xof = X
   → DriverCashDebt créé : {driver_id, restaurant_id, amount = X - delivery_fee}
   → Order.payout_status = 'cash_pending'
   → Order.payment_status = completed
4. App livreur affiche : "Tu dois reverser Y F à [Restaurant]"
5. Livreur envoie via Wave/Orange
6. Livreur déclare le reversement dans l'app :
   → POST /driver/cash-remittances {restaurant_id, amount, method, wave_ref?}
   → DriverCashRemittance créé (status = pending)
   → Notification au restaurant
7. Restaurant confirme réception :
   → PATCH /dashboard/orders/{id}/confirm-cash-remittance
   → DriverCashRemittance.status = confirmed
   → DriverCashDebt soldée
   → Order.payout_status = completed
8. DriverEarning créé : gross = delivery_fee, net = 80%, status = 'available'
   (NB: pour cash, DriverEarning créé APRÈS confirmation du reversement, pas à DELIVERED)
```

---

## Composant 3 — Tarification par quartier

### Problème actuel
Le système calcule `base_fee + distance_km × fee_per_km`. Les livreurs CI facturent par quartier.

### Nouvelle table : `delivery_zone_prices`

```sql
CREATE TABLE delivery_zone_prices (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    from_zone_id    BIGINT UNSIGNED NOT NULL,  -- zone du restaurant
    to_zone_id      BIGINT UNSIGNED NULL,       -- NULL = fallback (hors zone connue)
    price_xof       INT UNSIGNED NOT NULL,      -- prix en FCFA entiers
    is_active       BOOLEAN DEFAULT true,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP,
    UNIQUE KEY uq_zone_pair (from_zone_id, to_zone_id)
);
```

### Logique de calcul (DeliveryPricingService)

```php
// Ordre de priorité :
// 1. Chercher DeliveryZonePrice (from_zone = zone restaurant, to_zone = zone client)
// 2. Fallback : DeliveryZonePrice avec to_zone IS NULL (prix hors-zone)
// 3. Fallback final : DeliveryCity.delivery_base_fee + distance × fee_per_km

public function calculateByZone(int $fromZoneId, ?int $toZoneId): int
{
    // Chercher prix exact
    $price = DeliveryZonePrice::where('from_zone_id', $fromZoneId)
        ->where('to_zone_id', $toZoneId)
        ->where('is_active', true)
        ->first();

    if ($price) return $price->price_xof;

    // Fallback hors-zone
    $fallback = DeliveryZonePrice::where('from_zone_id', $fromZoneId)
        ->whereNull('to_zone_id')
        ->where('is_active', true)
        ->first();

    return $fallback?->price_xof ?? $this->calculateByKm($fromZoneId);
}
```

### Exemple Daloa

| Zone restaurant | Zone client | Prix |
|----------------|-------------|------|
| Tazibouo | Tazibouo (même) | 500 F |
| Tazibouo | Orly | 1 000 F |
| Tazibouo | Corridor | 1 500 F |
| Tazibouo | NULL (hors-zone) | 2 000 F |

### Interface Super Admin (matrice)

Page `/admin/delivery-cities/{city}/zones/pricing` :
- Grille from_zone × to_zone
- Chaque cellule = input prix en FCFA
- Sauvegarde par POST

---

## Composant 4 — Tables de dette livreur

### `driver_cash_debts`

```sql
CREATE TABLE driver_cash_debts (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    driver_id       BIGINT UNSIGNED NOT NULL,
    restaurant_id   BIGINT UNSIGNED NOT NULL,
    order_id        BIGINT UNSIGNED NOT NULL,
    delivery_id     BIGINT UNSIGNED NOT NULL,
    amount_xof      INT UNSIGNED NOT NULL,   -- montant dû au restaurant
    status          ENUM('pending','settled') DEFAULT 'pending',
    settled_at      TIMESTAMP NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);
```

### `driver_cash_remittances`

```sql
CREATE TABLE driver_cash_remittances (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    driver_id       BIGINT UNSIGNED NOT NULL,
    restaurant_id   BIGINT UNSIGNED NOT NULL,
    debt_id         BIGINT UNSIGNED NOT NULL,
    amount_xof      INT UNSIGNED NOT NULL,
    method          ENUM('wave','orange_money','mtn_money','moov_money','cash'),
    wave_reference  VARCHAR(100) NULL,
    status          ENUM('pending','confirmed','disputed') DEFAULT 'pending',
    confirmed_by    BIGINT UNSIGNED NULL,
    confirmed_at    TIMESTAMP NULL,
    note            TEXT NULL,
    created_at      TIMESTAMP,
    updated_at      TIMESTAMP
);
```

### Modifications table `deliveries`

```sql
ALTER TABLE deliveries ADD COLUMN cash_collected BOOLEAN DEFAULT false;
ALTER TABLE deliveries ADD COLUMN cash_collected_amount_xof INT UNSIGNED NULL;
ALTER TABLE deliveries ADD COLUMN cash_owed_to_restaurant_xof INT UNSIGNED NULL;
```

---

## Composant 5 — Nouveaux endpoints API livreur

### Confirmer collecte cash
```
POST /api/v1/driver/deliveries/{id}/cash-collected
Body: { amount_collected: 5500 }
→ Delivery.cash_collected = true
→ Delivery.cash_collected_amount_xof = 5500
→ Delivery.cash_owed_to_restaurant_xof = 5000 (= amount - delivery_fee)
→ DriverCashDebt::create(...)
→ Order.payment_status = completed
→ Order.payout_status = cash_pending
→ Notification restaurant "Votre argent sera reversé par [livreur]"
```

### Déclarer un reversement
```
POST /api/v1/driver/cash-remittances
Body: { restaurant_id, amount, method, wave_reference? }
→ DriverCashRemittance::create(status=pending)
→ Notification restaurant
```

### Solde de dette
```
GET /api/v1/driver/cash-balance
Response: {
  total_owed_xof: 5000,
  debts: [
    { restaurant_name: "Maquis Chez Awa", amount_xof: 5000, order_ref: "CMD-xxx" }
  ]
}
```

### Historique reversements
```
GET /api/v1/driver/cash-remittances
Response: liste paginée DriverCashRemittance
```

### Confirmer réception (côté restaurant — Livewire)
```
Action Livewire : confirmCashRemittance($remittanceId)
→ DriverCashRemittance.status = confirmed
→ DriverCashDebt.status = settled
→ Order.payout_status = completed
→ DriverEarning créé (maintenant que la dette est soldée)
```

---

## Composant 6 — Fix critique : creditDriverEarning

### Bug actuel
`creditDriverEarning()` est appelé à chaque `DELIVERED`, même pour `cash_on_delivery`. Résultat : le livreur accumule des gains Wave alors qu'il a encore l'argent cash du restaurant.

### Fix
```php
private function creditDriverEarning(Delivery $delivery): void
{
    $order = $delivery->order;

    // NE PAS créditer pour cash_on_delivery
    // Les gains seront crédités après confirmation du reversement
    if ($order->payment_method === 'cash_on_delivery') {
        return;
    }

    // ... reste du code inchangé
}
```

### Quand créditer pour cash_on_delivery ?
Dans `confirmCashRemittance()` côté restaurant (après confirmation réception argent).

---

## Vue restaurant — Nouvelles interfaces

### Page Orders → commande DELIVERING/DELIVERED (source != platform_app)
```
[Marquer comme livré]   [Argent reçu] (si cash_on_delivery)
```

### Page Orders → onglet "Reversements en attente"
Liste des `DriverCashRemittance.status = pending` :
```
Livreur Kouamé — 5 000 F — Wave — Réf: W123456
[Confirmer réception]  [Signaler un problème]
```

---

## Vue livreur — App (nouveaux écrans)

### Écran livraison active (cash_on_delivery)
```
┌──────────────────────────────────┐
│ Commande CMD-123                 │
│ Maquis Chez Awa → Tazibouo      │
│                                  │
│ Total à collecter : 5 500 F     │
│ [J'ai collecté l'argent ✓]      │
└──────────────────────────────────┘
```

### Écran "Mes dettes" (après collecte)
```
┌──────────────────────────────────┐
│ Tu dois reverser :               │
│ Maquis Chez Awa : 5 000 F       │
│                                  │
│ [Déclarer un reversement]        │
│   Via: [Wave] [Orange] [Cash]   │
│   Réf Wave: ___________         │
│   [Confirmer]                    │
└──────────────────────────────────┘
```

---

## Règle de blocage payout Wave livreur

Dans `EarningsController::requestPayout()` :
```php
$totalDebt = DriverCashDebt::where('driver_id', $driver->id)
    ->where('status', 'pending')
    ->sum('amount_xof');

if ($totalDebt > 25000) {  // seuil : 25 000 F
    return response()->json([
        'error' => 'Tu as ' . number_format($totalDebt) . ' F à reverser avant de retirer tes gains.'
    ], 422);
}
```

---

## Plan d'implémentation — 5 tâches séquentielles

### Tâche 1 : Fix critique (0.5j)
- `creditDriverEarning()` ne pas créditer sur cash_on_delivery
- Garde dans `ProcessJekoPayoutJob` : skip si cash_on_delivery

### Tâche 2 : Migrations (1j)
- `delivery_zone_prices`
- `driver_cash_debts`
- `driver_cash_remittances`
- Colonnes sur `deliveries` (cash_collected, cash_collected_amount_xof, cash_owed_to_restaurant_xof)

### Tâche 3 : Mode Restaurant backoffice (1j)
- Bouton "Marquer comme livré" dans Orders (source != platform_app, type = delivery)
- Bouton "Argent reçu" (cash_on_delivery)
- Onglet "Reversements en attente" dans Orders

### Tâche 4 : API livreur cash (2j)
- `POST /driver/deliveries/{id}/cash-collected`
- `POST /driver/cash-remittances`
- `GET /driver/cash-balance`
- Blocage payout si dette > 25 000 F

### Tâche 5 : Tarification par quartier (2j)
- Migration `delivery_zone_prices`
- `DeliveryPricingService::calculateByZone()`
- Interface Super Admin matrice de prix
- Checkout utilise la nouvelle logique

---

## Contraintes globales

- Montants toujours en **FCFA entiers** (pas de centimes)
- `Order.source` détermine le mode ('platform_app' = livreurs MenuPro, autres = restaurant)
- `payment_method = 'cash_on_delivery'` + `source = 'platform_app'` = seul cas avec dette livreur
- `payment_method = 'cash_on_delivery'` + `source != 'platform_app'` = restaurant gère tout seul
- Commission MenuPro (20%) uniquement sur les courses des livreurs plateforme
- Seuil blocage payout : 25 000 F de dette active

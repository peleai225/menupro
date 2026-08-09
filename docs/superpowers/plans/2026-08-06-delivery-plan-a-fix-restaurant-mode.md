# Livraison Plan A — Fix Critique + Mode Restaurant

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Corriger le bug critique du crédit DriverEarning sur cash_on_delivery, et ajouter les boutons "Marquer comme livré" + "Argent reçu" dans le backoffice restaurant pour les commandes gérées par leurs propres livreurs.

**Architecture:** Deux modifications de code existant sans nouvelles tables. (1) `DriverAssignmentService::creditDriverEarning()` ignore les commandes cash_on_delivery — les gains seront crédités dans le Plan B après confirmation du reversement. (2) Le composant Livewire `Orders` reçoit deux nouvelles actions pour les commandes `type=delivery` et `source != platform_app`.

**Tech Stack:** Laravel 11, PHP 8.3, Livewire 3, Tailwind CSS. Pas de migration.

## Global Constraints

- Montants en FCFA entiers (pas de centimes). `Order.delivery_fee` est déjà en entiers XOF.
- `Order.source` valeurs : `'pos'`, `'platform_web'`, `'platform_app'` — seul `'platform_app'` utilise les livreurs MenuPro.
- `Order.payment_method` valeur pour paiement à la livraison : `'cash_on_delivery'`.
- `Order.payout_status` valeurs existantes : `null`, `'pending'`, `'completed'`, `'failed'`, `'manual'`.
- Ne pas toucher au flux Jeko/Wave (paiements en ligne) — déjà correct.
- Les boutons "livraison restaurant" n'apparaissent QUE si `$order->type->value === 'delivery'` ET `$order->source !== 'platform_app'`.

---

### Task 1 : Fix `creditDriverEarning()` — ne pas créditer sur cash_on_delivery

**Files:**
- Modify: `app/Services/DriverAssignmentService.php:189-210`

**Interfaces:**
- Consumes: `Delivery $delivery` avec `$delivery->order->payment_method` (string)
- Produces: méthode modifiée — retourne void sans créer `DriverEarning` si cash_on_delivery

- [ ] **Step 1 : Lire la méthode actuelle**

Ouvre `app/Services/DriverAssignmentService.php` et repère `creditDriverEarning()` (ligne ~189).
Vérifie qu'elle ne contient aucune garde sur `payment_method` actuellement.

- [ ] **Step 2 : Ajouter la garde cash_on_delivery**

Remplace le début de `creditDriverEarning()` par :

```php
private function creditDriverEarning(Delivery $delivery): void
{
    $order = $delivery->order;

    // Pour cash_on_delivery, le livreur collecte l'argent physiquement.
    // Les gains seront crédités après confirmation du reversement au restaurant (Plan B).
    if ($order->payment_method === 'cash_on_delivery') {
        Log::info('creditDriverEarning: skipped for cash_on_delivery', [
            'delivery_id' => $delivery->id,
            'order_id'    => $order->id,
        ]);
        return;
    }

    $gross = $order->delivery_fee;

    if ($gross <= 0) {
        return;
    }
    // ... reste du code inchangé
```

- [ ] **Step 3 : Ajouter la garde dans `ProcessJekoPayoutJob`**

Ouvre `app/Jobs/ProcessJekoPayoutJob.php`. Après la garde `payment_status->isSuccessful()` (ligne ~51), ajoute :

```php
// Ne pas reverser via Jeko pour les paiements cash — le livreur collecte directement.
if ($this->order->payment_method === 'cash_on_delivery') {
    Log::channel('payments')->info('ProcessJekoPayoutJob: skipped for cash_on_delivery', [
        'order_id' => $this->order->id,
    ]);
    return;
}
```

- [ ] **Step 4 : Vérifier la syntaxe**

```bash
php -l app/Services/DriverAssignmentService.php
php -l app/Jobs/ProcessJekoPayoutJob.php
```

Expected : `No syntax errors detected`

- [ ] **Step 5 : Commit**

```bash
git add app/Services/DriverAssignmentService.php app/Jobs/ProcessJekoPayoutJob.php
git commit -m "fix(delivery): skip DriverEarning credit and Jeko payout for cash_on_delivery

Cash_on_delivery = livreur collecte physiquement l'argent du client.
Créditer ses gains Wave avant qu'il reverse au restaurant est incorrect.
Les gains seront crédités après confirmation du reversement (Plan B).
ProcessJekoPayoutJob ne doit pas déclencher un payout Jeko pour du cash."
```

---

### Task 2 : Nouvelles actions Livewire dans `Orders.php`

**Files:**
- Modify: `app/Livewire/Restaurant/Orders.php`

**Interfaces:**
- Consumes: `Order` avec `type`, `source`, `payment_method`, `status`, `payout_status`
- Produces:
  - `public function markDelivered(int $orderId): void` — marque COMPLETED
  - `public function markCashReceived(int $orderId): void` — marque payment_status=completed, payout_status=manual

- [ ] **Step 1 : Lire la fin du fichier Orders.php**

```bash
grep -n "public function\|updateStatus\|openCancelModal" app/Livewire/Restaurant/Orders.php | tail -20
```

Repère les méthodes existantes pour comprendre le pattern (authorize, find order, update).

- [ ] **Step 2 : Ajouter `markDelivered()`**

À la fin du fichier `app/Livewire/Restaurant/Orders.php`, avant le `render()`, ajoute :

```php
/**
 * Mode Restaurant : le restaurant marque la commande comme livrée.
 * Uniquement pour les commandes delivery dont les livreurs sont gérés par le restaurant (source != platform_app).
 */
public function markDelivered(int $orderId): void
{
    $order = \App\Models\Order::where('restaurant_id', $this->restaurant->id)
        ->where('type', \App\Enums\OrderType::DELIVERY)
        ->where('status', \App\Enums\OrderStatus::DELIVERING)
        ->findOrFail($orderId);

    if ($order->source === 'platform_app') {
        session()->flash('error', 'Cette commande est gérée par les livreurs MenuPro.');
        return;
    }

    $order->update([
        'status'       => \App\Enums\OrderStatus::COMPLETED,
        'completed_at' => now(),
    ]);

    $this->selectedOrder = $order->fresh();
    session()->flash('success', 'Commande marquée comme livrée.');
}

/**
 * Mode Restaurant : le restaurant confirme avoir reçu l'argent cash.
 * Uniquement pour cash_on_delivery géré par le restaurant lui-même.
 */
public function markCashReceived(int $orderId): void
{
    $order = \App\Models\Order::where('restaurant_id', $this->restaurant->id)
        ->where('type', \App\Enums\OrderType::DELIVERY)
        ->where('payment_method', 'cash_on_delivery')
        ->findOrFail($orderId);

    if ($order->source === 'platform_app') {
        session()->flash('error', 'Utilisez la section Reversements pour les livraisons MenuPro.');
        return;
    }

    $order->update([
        'payment_status' => \App\Enums\PaymentStatus::COMPLETED,
        'paid_at'        => now(),
        'payout_status'  => 'manual',
    ]);

    $this->selectedOrder = $order->fresh();
    session()->flash('success', 'Paiement cash confirmé.');
}
```

- [ ] **Step 3 : Vérifier la syntaxe**

```bash
php -l app/Livewire/Restaurant/Orders.php
```

Expected : `No syntax errors detected`

- [ ] **Step 4 : Commit**

```bash
git add app/Livewire/Restaurant/Orders.php
git commit -m "feat(orders): add markDelivered and markCashReceived actions for restaurant-mode delivery"
```

---

### Task 3 : Boutons dans la vue `orders.blade.php`

**Files:**
- Modify: `resources/views/livewire/restaurant/orders.blade.php`

**Interfaces:**
- Consumes: `$selectedOrder->type`, `$selectedOrder->source`, `$selectedOrder->status`, `$selectedOrder->payment_method`
- Produces: Bloc de boutons conditionnels dans le footer du modal commande

- [ ] **Step 1 : Repérer le footer des actions dans la vue**

```bash
grep -n "Actions Footer\|unless.*is_final\|flex-shrink-0.*border-t" resources/views/livewire/restaurant/orders.blade.php | head -10
```

Repère le bloc `@unless($selectedOrder->is_final)` qui contient les boutons d'action.

- [ ] **Step 2 : Lire le bloc d'actions existant**

Lis les lignes du footer (environ 20 lignes autour du résultat précédent) pour comprendre la structure des boutons existants.

- [ ] **Step 3 : Ajouter les boutons livraison restaurant**

Dans le footer des actions, après le bloc existant des boutons (Confirmer, Préparer, etc.), avant `@endunless`, ajoute ce bloc conditionnel :

```blade
{{-- Boutons spécifiques : livraison gérée par le restaurant (pas platform_app) --}}
@if(
    $selectedOrder->type->value === 'delivery' &&
    $selectedOrder->source !== 'platform_app' &&
    !$selectedOrder->is_final
)
    {{-- Marquer comme livré (quand commande en DELIVERING) --}}
    @if($selectedOrder->status->value === 'delivering')
    <div class="flex-shrink-0 border-t border-neutral-200 px-4 py-3 flex gap-2" style="background:#f0fdf4">
        <button wire:click="markDelivered({{ $selectedOrder->id }})"
                wire:loading.attr="disabled"
                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl font-bold text-white text-sm touch-manipulation min-h-[52px]"
                style="background:linear-gradient(135deg,#16a34a,#15803d)">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span wire:loading.remove wire:target="markDelivered">Marquer comme livré</span>
            <span wire:loading wire:target="markDelivered">...</span>
        </button>
    </div>
    @endif

    {{-- Confirmer réception argent cash (quand COMPLETED + cash_on_delivery + pas encore payé) --}}
    @if(
        $selectedOrder->payment_method === 'cash_on_delivery' &&
        !$selectedOrder->is_paid
    )
    <div class="flex-shrink-0 border-t border-neutral-200 px-4 py-3 flex gap-2" style="background:#fffbeb">
        <div class="flex-1">
            <p class="text-xs text-amber-700 font-semibold mb-2">
                💵 Paiement cash à la livraison — confirmez la réception
            </p>
            <button wire:click="markCashReceived({{ $selectedOrder->id }})"
                    wire:loading.attr="disabled"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-3.5 rounded-xl font-bold text-white text-sm touch-manipulation min-h-[52px]"
                    style="background:linear-gradient(135deg,#d97706,#b45309)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span wire:loading.remove wire:target="markCashReceived">J'ai reçu l'argent cash</span>
                <span wire:loading wire:target="markCashReceived">...</span>
            </button>
        </div>
    </div>
    @endif
@endif
```

- [ ] **Step 4 : Vider le cache des vues**

```bash
php artisan view:clear
```

Expected : `Compiled views cleared successfully.`

- [ ] **Step 5 : Tester visuellement**

Cherche une commande de type `delivery` avec `source = 'pos'` ou `'platform_web'` dans le backoffice restaurant. Ouvre le modal et vérifie que les boutons apparaissent selon le statut :
- Statut `DELIVERING` → bouton vert "Marquer comme livré"
- Statut quelconque + `cash_on_delivery` + non payé → encart jaune "J'ai reçu l'argent cash"
- Commande `platform_app` → aucun de ces boutons

- [ ] **Step 6 : Commit**

```bash
git add resources/views/livewire/restaurant/orders.blade.php
git commit -m "feat(orders): add 'Marquer comme livré' and 'Argent reçu' buttons for restaurant-managed delivery

Only shown when:
- order.type = delivery
- order.source != platform_app (restaurant manages own drivers)
- Not yet final

'Marquer comme livré': appears when status = DELIVERING
'Argent reçu': appears when payment_method = cash_on_delivery and not paid"
```

---

### Task 4 : Push et déploiement

- [ ] **Step 1 : Vérifier qu'il n'y a pas de changements non committés**

```bash
git status
```

Expected : `nothing to commit`

- [ ] **Step 2 : Push**

```bash
git push origin main
```

- [ ] **Step 3 : Déployer sur le serveur**

```bash
# Sur le serveur menupro.ci :
bash ~/deploy.sh
```

- [ ] **Step 4 : Vérifier en production**

1. Connecte-toi en tant que restaurant sur menupro.ci
2. Crée une commande de livraison test (type = delivery)
3. Passe-la en statut DELIVERING
4. Ouvre le modal → vérifie le bouton "Marquer comme livré"
5. Si cash_on_delivery : vérifie l'encart jaune "Argent reçu"

---

## Self-Review

### Spec coverage
- ✅ Fix `creditDriverEarning` cash_on_delivery → Task 1
- ✅ Garde `ProcessJekoPayoutJob` cash_on_delivery → Task 1
- ✅ Bouton "Marquer comme livré" → Task 2 + 3
- ✅ Bouton "Argent reçu" (cash) → Task 2 + 3
- ✅ Condition `source != platform_app` → Tasks 2 et 3

### Type consistency
- `\App\Enums\OrderType::DELIVERY` → valeur string `'delivery'` ✓
- `\App\Enums\OrderStatus::DELIVERING` → valeur string `'delivering'` ✓
- `\App\Enums\PaymentStatus::COMPLETED` → à vérifier (cf. `app/Enums/PaymentStatus.php`)
- `$order->is_final` → propriété existante sur le modèle Order ✓
- `$order->is_paid` → propriété existante sur le modèle Order ✓

### Placeholder scan
Aucun TBD. Chaque step contient le code exact. ✓

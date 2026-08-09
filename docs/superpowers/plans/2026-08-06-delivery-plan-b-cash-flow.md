# Livraison Plan B — Cash Flow Livreur Plateforme

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implémenter le système de dette livreur pour les commandes cash_on_delivery gérées par les livreurs MenuPro (source=platform_app) : le livreur confirme la collecte, déclare le reversement Wave, et le restaurant confirme la réception.

**Architecture:** 3 nouvelles tables (`driver_cash_debts`, `driver_cash_remittances`, colonnes sur `deliveries`), 4 nouveaux endpoints API livreur, 1 nouvelle section "Reversements" dans le backoffice restaurant, et la logique de blocage du payout Wave si dette > 25 000 F. Le Plan A doit être déployé avant ce plan (il corrige le bug critique).

**Tech Stack:** Laravel 11, PHP 8.3, Livewire 3, Tailwind CSS, Sanctum API.

## Global Constraints

- Montants en FCFA entiers. `delivery_fee` sur Order = entier XOF.
- `Order.source = 'platform_app'` = seul cas où ce plan s'applique.
- `Order.payment_method = 'cash_on_delivery'` = seul cas où la dette livreur est créée.
- Seuil blocage payout Wave : 25 000 F de dette active non soldée.
- `DriverEarning` créé APRÈS confirmation du reversement restaurant (pas à DELIVERED).
- `DriverEarning.status = 'available'` quand créé après reversement confirmé.
- Tous les modèles dans `app/Models/`, tous les controllers API dans `app/Http/Controllers/Api/V1/Driver/`.

---

### Task 1 : Migration — 3 nouvelles tables + colonnes deliveries

**Files:**
- Create: `database/migrations/2026_08_06_000001_add_cash_fields_to_deliveries.php`
- Create: `database/migrations/2026_08_06_000002_create_driver_cash_debts_table.php`
- Create: `database/migrations/2026_08_06_000003_create_driver_cash_remittances_table.php`

**Interfaces:**
- Produces: tables `driver_cash_debts` et `driver_cash_remittances`, colonnes `cash_collected`, `cash_collected_amount_xof`, `cash_owed_to_restaurant_xof` sur `deliveries`

- [ ] **Step 1 : Créer la migration colonnes deliveries**

```bash
php artisan make:migration add_cash_fields_to_deliveries --table=deliveries
```

Remplis le fichier généré avec :

```php
public function up(): void
{
    Schema::table('deliveries', function (Blueprint $table) {
        $table->boolean('cash_collected')->default(false)->after('delivered_at');
        $table->unsignedInteger('cash_collected_amount_xof')->nullable()->after('cash_collected');
        $table->unsignedInteger('cash_owed_to_restaurant_xof')->nullable()->after('cash_collected_amount_xof');
    });
}

public function down(): void
{
    Schema::table('deliveries', function (Blueprint $table) {
        $table->dropColumn(['cash_collected', 'cash_collected_amount_xof', 'cash_owed_to_restaurant_xof']);
    });
}
```

- [ ] **Step 2 : Créer la migration driver_cash_debts**

```bash
php artisan make:migration create_driver_cash_debts_table
```

```php
public function up(): void
{
    Schema::create('driver_cash_debts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('driver_id')->constrained('delivery_drivers')->cascadeOnDelete();
        $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
        $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
        $table->foreignId('delivery_id')->constrained('deliveries')->cascadeOnDelete();
        $table->unsignedInteger('amount_xof');
        $table->enum('status', ['pending', 'settled'])->default('pending');
        $table->timestamp('settled_at')->nullable();
        $table->timestamps();

        $table->index(['driver_id', 'status']);
        $table->index(['restaurant_id', 'status']);
    });
}

public function down(): void
{
    Schema::dropIfExists('driver_cash_debts');
}
```

- [ ] **Step 3 : Créer la migration driver_cash_remittances**

```bash
php artisan make:migration create_driver_cash_remittances_table
```

```php
public function up(): void
{
    Schema::create('driver_cash_remittances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('driver_id')->constrained('delivery_drivers')->cascadeOnDelete();
        $table->foreignId('restaurant_id')->constrained('restaurants')->cascadeOnDelete();
        $table->foreignId('debt_id')->constrained('driver_cash_debts')->cascadeOnDelete();
        $table->unsignedInteger('amount_xof');
        $table->enum('method', ['wave', 'orange_money', 'mtn_money', 'moov_money', 'cash']);
        $table->string('wave_reference', 100)->nullable();
        $table->enum('status', ['pending', 'confirmed', 'disputed'])->default('pending');
        $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('confirmed_at')->nullable();
        $table->text('note')->nullable();
        $table->timestamps();

        $table->index(['driver_id', 'status']);
        $table->index(['restaurant_id', 'status']);
    });
}

public function down(): void
{
    Schema::dropIfExists('driver_cash_remittances');
}
```

- [ ] **Step 4 : Lancer les migrations**

```bash
php artisan migrate
```

Expected : 3 migrations appliquées sans erreur.

- [ ] **Step 5 : Commit**

```bash
git add database/migrations/
git commit -m "feat(delivery): add cash flow tables (driver_cash_debts, remittances, delivery columns)"
```

---

### Task 2 : Modèles Eloquent

**Files:**
- Create: `app/Models/DriverCashDebt.php`
- Create: `app/Models/DriverCashRemittance.php`
- Modify: `app/Models/Delivery.php` (ajouter fillable + casts)

**Interfaces:**
- Produces:
  - `DriverCashDebt::pendingForDriver(int $driverId): Collection`
  - `DriverCashDebt::totalPendingForDriver(int $driverId): int`
  - `DriverCashRemittance` avec relations `debt()`, `driver()`, `restaurant()`

- [ ] **Step 1 : Créer DriverCashDebt**

```php
<?php
// app/Models/DriverCashDebt.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DriverCashDebt extends Model
{
    protected $fillable = [
        'driver_id', 'restaurant_id', 'order_id', 'delivery_id',
        'amount_xof', 'status', 'settled_at',
    ];

    protected $casts = [
        'amount_xof' => 'integer',
        'settled_at' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function remittance(): HasOne
    {
        return $this->hasOne(DriverCashRemittance::class, 'debt_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public static function pendingForDriver(int $driverId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('driver_id', $driverId)
            ->where('status', 'pending')
            ->with(['restaurant:id,name', 'order:id,reference'])
            ->get();
    }

    public static function totalPendingForDriver(int $driverId): int
    {
        return (int) static::where('driver_id', $driverId)
            ->where('status', 'pending')
            ->sum('amount_xof');
    }
}
```

- [ ] **Step 2 : Créer DriverCashRemittance**

```php
<?php
// app/Models/DriverCashRemittance.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverCashRemittance extends Model
{
    protected $fillable = [
        'driver_id', 'restaurant_id', 'debt_id', 'amount_xof',
        'method', 'wave_reference', 'status', 'confirmed_by', 'confirmed_at', 'note',
    ];

    protected $casts = [
        'amount_xof'    => 'integer',
        'confirmed_at'  => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function debt(): BelongsTo
    {
        return $this->belongsTo(DriverCashDebt::class, 'debt_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
```

- [ ] **Step 3 : Modifier Delivery.php — ajouter les nouveaux champs**

Dans `app/Models/Delivery.php`, dans le tableau `$fillable`, ajoute :

```php
'cash_collected',
'cash_collected_amount_xof',
'cash_owed_to_restaurant_xof',
```

Dans `$casts`, ajoute :

```php
'cash_collected'              => 'boolean',
'cash_collected_amount_xof'   => 'integer',
'cash_owed_to_restaurant_xof' => 'integer',
```

- [ ] **Step 4 : Vérifier la syntaxe**

```bash
php -l app/Models/DriverCashDebt.php
php -l app/Models/DriverCashRemittance.php
php -l app/Models/Delivery.php
```

Expected : `No syntax errors detected`

- [ ] **Step 5 : Commit**

```bash
git add app/Models/DriverCashDebt.php app/Models/DriverCashRemittance.php app/Models/Delivery.php
git commit -m "feat(delivery): add DriverCashDebt and DriverCashRemittance models"
```

---

### Task 3 : Endpoint `POST /driver/deliveries/{id}/cash-collected`

**Files:**
- Modify: `app/Http/Controllers/Api/V1/Driver/DeliveryController.php`
- Modify: `routes/api.php`

**Interfaces:**
- Consumes: `{ amount_collected: int }` (FCFA entiers)
- Produces:
  - `Delivery` mise à jour avec `cash_collected=true`, montants
  - `DriverCashDebt` créé
  - `Order.payment_status = completed`, `Order.payout_status = 'cash_pending'`

- [ ] **Step 1 : Ajouter la méthode dans DeliveryController**

Dans `app/Http/Controllers/Api/V1/Driver/DeliveryController.php`, ajoute la méthode :

```php
/**
 * Le livreur confirme avoir collecté l'argent cash auprès du client.
 * Crée une dette envers le restaurant et marque la commande comme payée.
 */
public function confirmCashCollected(Request $request, int $deliveryId): JsonResponse
{
    $data = $request->validate([
        'amount_collected' => 'required|integer|min:1',
    ]);

    $driver   = $request->user()->deliveryDriver;
    $delivery = \App\Models\Delivery::where('driver_id', $driver->id)
        ->whereIn('status', ['delivering', 'delivered'])
        ->findOrFail($deliveryId);

    $order = $delivery->order;

    if ($order->payment_method !== 'cash_on_delivery') {
        return response()->json(['error' => 'Cette commande n\'est pas en paiement cash.'], 422);
    }

    if ($delivery->cash_collected) {
        return response()->json(['error' => 'La collecte a déjà été confirmée.'], 422);
    }

    $amountCollected = (int) $data['amount_collected'];
    $amountOwed      = max(0, $amountCollected - (int) $order->delivery_fee);

    \Illuminate\Support\Facades\DB::transaction(function () use ($delivery, $order, $driver, $amountCollected, $amountOwed) {
        $delivery->update([
            'cash_collected'              => true,
            'cash_collected_amount_xof'   => $amountCollected,
            'cash_owed_to_restaurant_xof' => $amountOwed,
        ]);

        $order->update([
            'payment_status' => \App\Enums\PaymentStatus::COMPLETED,
            'paid_at'        => now(),
            'payout_status'  => 'cash_pending',
        ]);

        if ($amountOwed > 0) {
            \App\Models\DriverCashDebt::create([
                'driver_id'     => $driver->id,
                'restaurant_id' => $order->restaurant_id,
                'order_id'      => $order->id,
                'delivery_id'   => $delivery->id,
                'amount_xof'    => $amountOwed,
                'status'        => 'pending',
            ]);
        }

        \Illuminate\Support\Facades\Log::channel('payments')->info('Driver confirmed cash collected', [
            'driver_id'       => $driver->id,
            'delivery_id'     => $delivery->id,
            'amount_collected' => $amountCollected,
            'amount_owed'     => $amountOwed,
        ]);
    });

    return response()->json([
        'message'      => 'Collecte confirmée.',
        'amount_owed'  => $amountOwed,
        'restaurant'   => $order->restaurant->name,
    ]);
}
```

- [ ] **Step 2 : Enregistrer la route**

Dans `routes/api.php`, dans le groupe `driver` → `delivery.driver`, après la route `deliveries/{id}/decline`, ajoute :

```php
Route::post('/deliveries/{id}/cash-collected', [DeliveryController::class, 'confirmCashCollected'])->name('deliveries.cash-collected');
```

- [ ] **Step 3 : Vérifier la syntaxe et la route**

```bash
php -l app/Http/Controllers/Api/V1/Driver/DeliveryController.php
php artisan route:list | grep cash-collected
```

Expected : route `api.v1.driver.deliveries.cash-collected` listée en POST.

- [ ] **Step 4 : Commit**

```bash
git add app/Http/Controllers/Api/V1/Driver/DeliveryController.php routes/api.php
git commit -m "feat(driver-api): add POST /driver/deliveries/{id}/cash-collected endpoint"
```

---

### Task 4 : Endpoints solde et reversements (`GET cash-balance` + `POST cash-remittances`)

**Files:**
- Create: `app/Http/Controllers/Api/V1/Driver/CashController.php`
- Modify: `routes/api.php`

**Interfaces:**
- Produces:
  - `GET /driver/cash-balance` → `{ total_owed_xof: int, debts: [...] }`
  - `POST /driver/cash-remittances` → crée `DriverCashRemittance`

- [ ] **Step 1 : Créer CashController**

```php
<?php
// app/Http/Controllers/Api/V1/Driver/CashController.php
namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverCashDebt;
use App\Models\DriverCashRemittance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashController extends Controller
{
    /**
     * Solde des dettes cash du livreur envers les restaurants.
     */
    public function balance(Request $request): JsonResponse
    {
        $driver = $request->user()->deliveryDriver;
        $debts  = DriverCashDebt::pendingForDriver($driver->id);

        return response()->json([
            'total_owed_xof' => DriverCashDebt::totalPendingForDriver($driver->id),
            'debts' => $debts->map(fn($d) => [
                'id'              => $d->id,
                'restaurant_name' => $d->restaurant->name ?? '—',
                'order_ref'       => $d->order->reference ?? '—',
                'amount_xof'      => $d->amount_xof,
                'created_at'      => $d->created_at?->toDateTimeString(),
            ])->values(),
        ]);
    }

    /**
     * Le livreur déclare avoir reversé l'argent au restaurant.
     */
    public function storeRemittance(Request $request): JsonResponse
    {
        $data = $request->validate([
            'debt_id'        => 'required|integer|exists:driver_cash_debts,id',
            'amount_xof'     => 'required|integer|min:1',
            'method'         => 'required|in:wave,orange_money,mtn_money,moov_money,cash',
            'wave_reference' => 'nullable|string|max:100',
            'note'           => 'nullable|string|max:500',
        ]);

        $driver = $request->user()->deliveryDriver;

        $debt = DriverCashDebt::where('id', $data['debt_id'])
            ->where('driver_id', $driver->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $remittance = DriverCashRemittance::create([
            'driver_id'      => $driver->id,
            'restaurant_id'  => $debt->restaurant_id,
            'debt_id'        => $debt->id,
            'amount_xof'     => (int) $data['amount_xof'],
            'method'         => $data['method'],
            'wave_reference' => $data['wave_reference'] ?? null,
            'status'         => 'pending',
            'note'           => $data['note'] ?? null,
        ]);

        // Notifier le restaurant (si notifications push configurées)
        try {
            $restaurant = $debt->restaurant;
            if ($restaurant) {
                \Illuminate\Support\Facades\Log::channel('payments')->info('Driver declared cash remittance', [
                    'driver_id'      => $driver->id,
                    'restaurant_id'  => $debt->restaurant_id,
                    'amount_xof'     => $remittance->amount_xof,
                    'method'         => $remittance->method,
                    'wave_reference' => $remittance->wave_reference,
                ]);
            }
        } catch (\Throwable $e) {
            // Notification non critique
        }

        return response()->json([
            'message'      => 'Reversement déclaré. En attente de confirmation du restaurant.',
            'remittance_id' => $remittance->id,
        ], 201);
    }

    /**
     * Historique des reversements déclarés.
     */
    public function remittances(Request $request): JsonResponse
    {
        $driver      = $request->user()->deliveryDriver;
        $remittances = DriverCashRemittance::where('driver_id', $driver->id)
            ->with(['restaurant:id,name', 'debt:id,amount_xof'])
            ->latest()
            ->paginate(20);

        return response()->json($remittances);
    }
}
```

- [ ] **Step 2 : Enregistrer les routes**

Dans `routes/api.php`, dans le groupe `driver` → `delivery.driver`, ajoute après les routes earnings :

```php
// Cash on delivery — gestion des dettes et reversements
Route::get('/cash-balance',          [\App\Http\Controllers\Api\V1\Driver\CashController::class, 'balance'])->name('cash.balance');
Route::get('/cash-remittances',      [\App\Http\Controllers\Api\V1\Driver\CashController::class, 'remittances'])->name('cash.remittances');
Route::post('/cash-remittances',     [\App\Http\Controllers\Api\V1\Driver\CashController::class, 'storeRemittance'])->name('cash.remittances.store');
```

- [ ] **Step 3 : Vérifier syntaxe et routes**

```bash
php -l app/Http/Controllers/Api/V1/Driver/CashController.php
php artisan route:list | grep "cash"
```

Expected : 3 routes listées sous `api.v1.driver.cash.*`

- [ ] **Step 4 : Commit**

```bash
git add app/Http/Controllers/Api/V1/Driver/CashController.php routes/api.php
git commit -m "feat(driver-api): add cash balance and remittance endpoints for cash_on_delivery"
```

---

### Task 5 : Blocage payout Wave si dette > 25 000 F

**Files:**
- Modify: `app/Http/Controllers/Api/V1/Driver/EarningsController.php`

**Interfaces:**
- Consumes: `DriverCashDebt::totalPendingForDriver(int $driverId): int`
- Produces: `requestPayout()` retourne 422 si dette > 25 000 F

- [ ] **Step 1 : Lire le début de `requestPayout()`**

```bash
sed -n '85,105p' app/Http/Controllers/Api/V1/Driver/EarningsController.php
```

Repère où les validations se font au début de la méthode.

- [ ] **Step 2 : Ajouter la garde dette après la validation**

Dans `EarningsController::requestPayout()`, après la ligne `$driver = $request->user()->deliveryDriver;`, ajoute :

```php
// Bloquer le payout si le livreur a trop de dettes cash en attente
$totalCashDebt = \App\Models\DriverCashDebt::totalPendingForDriver($driver->id);
$debtThreshold = 25000; // 25 000 FCFA
if ($totalCashDebt > $debtThreshold) {
    return response()->json([
        'message'       => 'Tu as ' . number_format($totalCashDebt, 0, ',', ' ') . ' F à reverser aux restaurants avant de retirer tes gains.',
        'cash_debt_xof' => $totalCashDebt,
    ], 422);
}
```

- [ ] **Step 3 : Vérifier syntaxe**

```bash
php -l app/Http/Controllers/Api/V1/Driver/EarningsController.php
```

Expected : `No syntax errors detected`

- [ ] **Step 4 : Commit**

```bash
git add app/Http/Controllers/Api/V1/Driver/EarningsController.php
git commit -m "feat(driver-api): block Wave payout if cash debt > 25000 XOF"
```

---

### Task 6 : Confirmation reversement côté restaurant (Livewire)

**Files:**
- Modify: `app/Livewire/Restaurant/Orders.php`
- Modify: `resources/views/livewire/restaurant/orders.blade.php`

**Interfaces:**
- Consumes: `DriverCashRemittance` avec `status=pending` liés aux commandes du restaurant
- Produces:
  - Action `confirmRemittance(int $remittanceId): void`
  - Section "Reversements en attente" dans la page Orders

- [ ] **Step 1 : Ajouter la propriété et la méthode dans Orders.php**

Dans `app/Livewire/Restaurant/Orders.php`, ajoute :

```php
// Dans mount() ou au début du composant, importer DriverCashRemittance
use App\Models\DriverCashRemittance;
use App\Models\DriverCashDebt;
use App\Services\DriverAssignmentService;
```

Ajoute la méthode :

```php
/**
 * Restaurant confirme avoir reçu le reversement cash du livreur.
 */
public function confirmRemittance(int $remittanceId): void
{
    $remittance = \App\Models\DriverCashRemittance::where('restaurant_id', $this->restaurant->id)
        ->where('status', 'pending')
        ->findOrFail($remittanceId);

    \Illuminate\Support\Facades\DB::transaction(function () use ($remittance) {
        // Confirmer le reversement
        $remittance->update([
            'status'       => 'confirmed',
            'confirmed_by' => auth()->id(),
            'confirmed_at' => now(),
        ]);

        // Solder la dette
        $debt = $remittance->debt;
        $debt->update([
            'status'      => 'settled',
            'settled_at'  => now(),
        ]);

        // Marquer la commande comme soldée
        $order = $debt->order;
        $order->update(['payout_status' => 'completed']);

        // Maintenant créditer les gains du livreur (différé depuis DELIVERED)
        $delivery = $debt->delivery;
        if ($delivery && $delivery->driver_id) {
            $gross       = (int) $order->delivery_fee;
            $platformCut = (int) round($gross * 0.20);
            $net         = $gross - $platformCut;

            if ($gross > 0) {
                \App\Models\DriverEarning::create([
                    'driver_id'    => $delivery->driver_id,
                    'order_id'     => $order->id,
                    'delivery_id'  => $delivery->id,
                    'gross_amount' => $gross,
                    'platform_cut' => $platformCut,
                    'net_amount'   => $net,
                    'status'       => 'available',
                ]);

                \App\Models\DeliveryDriver::where('id', $delivery->driver_id)
                    ->increment('total_earnings_xof', $net);
            }
        }
    });

    session()->flash('success', 'Reversement confirmé. Les gains du livreur ont été crédités.');
    $this->dispatch('$refresh');
}
```

- [ ] **Step 2 : Ajouter la propriété computed pour les reversements en attente**

Dans `Orders.php`, ajoute une computed property :

```php
#[\Livewire\Attributes\Computed]
public function pendingRemittances()
{
    return \App\Models\DriverCashRemittance::where('restaurant_id', $this->restaurant->id)
        ->where('status', 'pending')
        ->with(['driver.user:id,name', 'debt.order:id,reference,total,delivery_fee'])
        ->latest()
        ->get();
}
```

- [ ] **Step 3 : Ajouter la section "Reversements" dans la vue orders.blade.php**

Au début de la page Orders (avant la liste des commandes), ajoute ce bloc conditionnel :

```blade
{{-- Section Reversements en attente (cash_on_delivery livreurs plateforme) --}}
@if($this->pendingRemittances->isNotEmpty())
<div class="card p-4 mb-6 border-2 border-amber-200 bg-amber-50">
    <h2 class="text-base font-black text-amber-900 mb-3 flex items-center gap-2">
        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Reversements en attente ({{ $this->pendingRemittances->count() }})
    </h2>
    <div class="space-y-3">
        @foreach($this->pendingRemittances as $rem)
        <div class="bg-white rounded-xl p-3 border border-amber-200 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <p class="font-bold text-sm text-neutral-800 truncate">
                    {{ $rem->driver?->user?->name ?? 'Livreur' }}
                </p>
                <p class="text-xs text-neutral-500">
                    Cmd {{ $rem->debt?->order?->reference ?? '—' }} ·
                    {{ strtoupper($rem->method) }}
                    @if($rem->wave_reference) · Réf: {{ $rem->wave_reference }} @endif
                </p>
            </div>
            <div class="text-right shrink-0">
                <p class="font-black text-base text-amber-700">{{ number_format($rem->amount_xof, 0, ',', ' ') }} F</p>
                <button wire:click="confirmRemittance({{ $rem->id }})"
                        wire:loading.attr="disabled"
                        wire:confirm="Confirmer la réception de {{ number_format($rem->amount_xof, 0, ',', ' ') }} F ?"
                        class="mt-1 text-xs font-bold text-white px-3 py-1.5 rounded-lg touch-manipulation"
                        style="background:#16a34a">
                    <span wire:loading.remove wire:target="confirmRemittance({{ $rem->id }})">Confirmer réception</span>
                    <span wire:loading wire:target="confirmRemittance({{ $rem->id }})">...</span>
                </button>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif
```

- [ ] **Step 4 : Vérifier la syntaxe**

```bash
php -l app/Livewire/Restaurant/Orders.php
php artisan view:clear
```

Expected : pas d'erreur.

- [ ] **Step 5 : Commit**

```bash
git add app/Livewire/Restaurant/Orders.php resources/views/livewire/restaurant/orders.blade.php
git commit -m "feat(orders): add pending cash remittances section with confirm button

When a driver declares a cash remittance, the restaurant sees it here.
Clicking 'Confirmer réception' settles the debt and credits driver earnings."
```

---

### Task 7 : Push et déploiement

- [ ] **Step 1 : Vérifier tous les commits**

```bash
git log --oneline -7
```

Expected : 6 nouveaux commits depuis le Plan A.

- [ ] **Step 2 : Push**

```bash
git push origin main
```

- [ ] **Step 3 : Déployer (migrations incluses)**

```bash
# Sur le serveur menupro.ci :
bash ~/deploy.sh
# deploy.sh lance php artisan migrate --force automatiquement
```

- [ ] **Step 4 : Vérifier les nouvelles tables en prod**

```bash
php artisan tinker --execute="echo \Schema::hasTable('driver_cash_debts') ? 'OK' : 'MISSING';"
php artisan tinker --execute="echo \Schema::hasTable('driver_cash_remittances') ? 'OK' : 'MISSING';"
```

Expected : `OK` pour les deux.

---

## Self-Review

### Spec coverage
- ✅ Colonnes `cash_collected`, `cash_owed_to_restaurant_xof` sur deliveries → Task 1
- ✅ Table `driver_cash_debts` → Task 1
- ✅ Table `driver_cash_remittances` → Task 1
- ✅ Modèles Eloquent complets → Task 2
- ✅ Endpoint `POST /driver/deliveries/{id}/cash-collected` → Task 3
- ✅ `Order.payout_status = 'cash_pending'` → Task 3
- ✅ `GET /driver/cash-balance` → Task 4
- ✅ `POST /driver/cash-remittances` → Task 4
- ✅ Blocage payout Wave si dette > 25 000 F → Task 5
- ✅ Confirmation reversement côté restaurant → Task 6
- ✅ `DriverEarning` créé APRÈS confirmation (pas à DELIVERED) → Task 6

### Type consistency
- `DriverCashDebt::totalPendingForDriver()` retourne `int` → utilisé dans Task 5 ✓
- `DriverCashDebt::pendingForDriver()` retourne `Collection` → utilisé dans Task 4 ✓
- `$debt->delivery` relation sur DriverCashDebt → définie dans Task 2 ✓
- `DriverEarning::create()` avec `gross_amount`, `platform_cut`, `net_amount` → pattern existant dans `DriverAssignmentService` ✓

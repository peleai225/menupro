# Livraison Plan C — Tarification par Quartier

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Remplacer le calcul de frais de livraison par kilomètre par une matrice de prix zone-à-zone, configurable par ville depuis le Super Admin, avec fallback sur l'ancien calcul km si aucune matrice n'est définie.

**Architecture:** Nouvelle table `delivery_zone_prices` (matrice from_zone → to_zone → price_xof). `DeliveryPricingService` modifié pour chercher en priorité la matrice puis fallback km. Interface Super Admin pour saisir la matrice. Le checkout utilise déjà `DeliveryPricingService` — pas de changement côté checkout.

**Tech Stack:** Laravel 11, PHP 8.3, Tailwind CSS. Plans A et B n'ont pas besoin d'être déployés avant ce plan — il est indépendant.

## Global Constraints

- Prix en FCFA **entiers** dans la colonne `price_xof` (pas de centimes).
- `DeliveryZone` existe avec `id`, `name`, `delivery_city_id`, `center_latitude`, `center_longitude`, `radius_km`, `is_active`.
- `DeliveryCity` existe avec `id`, `name`, `delivery_base_fee`, `delivery_fee_per_km`.
- `to_zone_id IS NULL` = prix fallback hors-zone pour une ville donnée.
- La matrice est symétrique (A→B = B→A) ou asymétrique selon config — on supporte les deux (chaque sens est une entrée séparée).
- Fallback final si aucune entrée trouvée : `DeliveryCity.delivery_base_fee + distance × fee_per_km` (calcul actuel).

---

### Task 1 : Migration `delivery_zone_prices`

**Files:**
- Create: `database/migrations/2026_08_06_100001_create_delivery_zone_prices_table.php`

**Interfaces:**
- Produces: table `delivery_zone_prices` avec index unique sur `(from_zone_id, to_zone_id)`

- [ ] **Step 1 : Créer la migration**

```bash
php artisan make:migration create_delivery_zone_prices_table
```

Remplis le fichier généré :

```php
public function up(): void
{
    Schema::create('delivery_zone_prices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('from_zone_id')
              ->constrained('delivery_zones')
              ->cascadeOnDelete();
        $table->foreignId('to_zone_id')
              ->nullable()  // NULL = fallback hors-zone
              ->constrained('delivery_zones')
              ->nullOnDelete();
        $table->unsignedInteger('price_xof');  // FCFA entiers
        $table->boolean('is_active')->default(true);
        $table->timestamps();

        // Un seul prix par couple de zones
        $table->unique(['from_zone_id', 'to_zone_id'], 'uq_zone_pair');
        $table->index(['from_zone_id', 'is_active']);
    });
}

public function down(): void
{
    Schema::dropIfExists('delivery_zone_prices');
}
```

- [ ] **Step 2 : Lancer la migration**

```bash
php artisan migrate
```

Expected : migration appliquée sans erreur.

- [ ] **Step 3 : Commit**

```bash
git add database/migrations/
git commit -m "feat(delivery): create delivery_zone_prices table for zone-based pricing"
```

---

### Task 2 : Modèle `DeliveryZonePrice`

**Files:**
- Create: `app/Models/DeliveryZonePrice.php`

**Interfaces:**
- Produces:
  - `DeliveryZonePrice::findPrice(int $fromZoneId, ?int $toZoneId): ?self`
  - Relations `fromZone()`, `toZone()`

- [ ] **Step 1 : Créer le modèle**

```php
<?php
// app/Models/DeliveryZonePrice.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryZonePrice extends Model
{
    protected $fillable = [
        'from_zone_id',
        'to_zone_id',
        'price_xof',
        'is_active',
    ];

    protected $casts = [
        'price_xof' => 'integer',
        'is_active' => 'boolean',
    ];

    public function fromZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'from_zone_id');
    }

    public function toZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'to_zone_id');
    }

    /**
     * Cherche le prix entre deux zones.
     * Si toZoneId est null ou introuvable, cherche le fallback (to_zone_id IS NULL).
     */
    public static function findPrice(int $fromZoneId, ?int $toZoneId): ?self
    {
        // 1. Prix exact from→to
        if ($toZoneId !== null) {
            $exact = static::where('from_zone_id', $fromZoneId)
                ->where('to_zone_id', $toZoneId)
                ->where('is_active', true)
                ->first();
            if ($exact) return $exact;
        }

        // 2. Fallback hors-zone (to_zone_id IS NULL)
        return static::where('from_zone_id', $fromZoneId)
            ->whereNull('to_zone_id')
            ->where('is_active', true)
            ->first();
    }
}
```

- [ ] **Step 2 : Vérifier la syntaxe**

```bash
php -l app/Models/DeliveryZonePrice.php
```

Expected : `No syntax errors detected`

- [ ] **Step 3 : Commit**

```bash
git add app/Models/DeliveryZonePrice.php
git commit -m "feat(delivery): add DeliveryZonePrice model with findPrice() helper"
```

---

### Task 3 : Modifier `DeliveryPricingService` pour utiliser la matrice

**Files:**
- Modify: `app/Services/DeliveryPricingService.php`

**Interfaces:**
- Consumes: `DeliveryZonePrice::findPrice()`, `DeliveryZone::containsPoint()`
- Produces: `calculateFee(float $restaurantLat, float $restaurantLng, float $customerLat, float $customerLng, DeliveryCity $city): int`

- [ ] **Step 1 : Lire DeliveryPricingService actuel**

```bash
cat app/Services/DeliveryPricingService.php
```

Comprends la méthode de calcul actuelle (base_fee + distance × fee_per_km).

- [ ] **Step 2 : Ajouter la méthode `calculateByZoneMatrix()`**

Dans `app/Services/DeliveryPricingService.php`, ajoute cette méthode :

```php
/**
 * Calcule le frais de livraison via la matrice zone-à-zone.
 * Retourne null si aucune matrice n'est définie pour ces zones.
 */
public function calculateByZoneMatrix(
    float $restaurantLat,
    float $restaurantLng,
    float $customerLat,
    float $customerLng,
    \App\Models\DeliveryCity $city
): ?int {
    // Trouver la zone du restaurant
    $restaurantZone = \App\Models\DeliveryZone::where('delivery_city_id', $city->id)
        ->where('is_active', true)
        ->get()
        ->first(fn($zone) => $zone->containsPoint($restaurantLat, $restaurantLng));

    if (!$restaurantZone) {
        return null; // Restaurant hors zone connue → fallback km
    }

    // Trouver la zone du client
    $customerZone = \App\Models\DeliveryZone::where('delivery_city_id', $city->id)
        ->where('is_active', true)
        ->get()
        ->first(fn($zone) => $zone->containsPoint($customerLat, $customerLng));

    // Chercher le prix dans la matrice
    $zonePrice = \App\Models\DeliveryZonePrice::findPrice(
        $restaurantZone->id,
        $customerZone?->id
    );

    return $zonePrice?->price_xof; // null si aucune entrée → fallback km
}
```

- [ ] **Step 3 : Modifier la méthode principale de calcul**

Dans `DeliveryPricingService`, trouve la méthode principale qui calcule le fee (ex: `calculate()` ou `getFee()`). Modifie-la pour appeler d'abord la matrice :

```php
public function calculate(
    float $restaurantLat,
    float $restaurantLng,
    float $customerLat,
    float $customerLng,
    \App\Models\DeliveryCity $city
): int {
    // 1. Essayer la matrice de zones
    $zonePrice = $this->calculateByZoneMatrix(
        $restaurantLat, $restaurantLng,
        $customerLat, $customerLng,
        $city
    );

    if ($zonePrice !== null) {
        return $zonePrice; // Prix fixe par zone trouvé
    }

    // 2. Fallback : calcul par kilomètre (comportement existant)
    $distanceKm = \App\Models\DeliveryCity::haversineKm(
        $restaurantLat, $restaurantLng,
        $customerLat, $customerLng
    );
    return $city->calculateFee($distanceKm);
}
```

- [ ] **Step 4 : Vérifier la syntaxe**

```bash
php -l app/Services/DeliveryPricingService.php
```

Expected : `No syntax errors detected`

- [ ] **Step 5 : Commit**

```bash
git add app/Services/DeliveryPricingService.php
git commit -m "feat(delivery): DeliveryPricingService uses zone matrix first, falls back to km calculation"
```

---

### Task 4 : Interface Super Admin — Matrice de prix par ville

**Files:**
- Create: `app/Http/Controllers/SuperAdmin/DeliveryZonePricingController.php`
- Modify: `routes/web.php`
- Create: `resources/views/pages/super-admin/delivery-zone-pricing.blade.php`

**Interfaces:**
- Produces:
  - `GET /admin/villes-livraison/{city}/tarifs` → formulaire grille
  - `POST /admin/villes-livraison/{city}/tarifs` → sauvegarde

- [ ] **Step 1 : Créer le contrôleur**

```php
<?php
// app/Http/Controllers/SuperAdmin/DeliveryZonePricingController.php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryCity;
use App\Models\DeliveryZone;
use App\Models\DeliveryZonePrice;
use Illuminate\Http\Request;

class DeliveryZonePricingController extends Controller
{
    public function index(DeliveryCity $city)
    {
        $zones  = DeliveryZone::where('delivery_city_id', $city->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Charger la matrice existante
        $prices = DeliveryZonePrice::where('from_zone_id', $zones->pluck('id'))
            ->get()
            ->keyBy(fn($p) => $p->from_zone_id . '_' . ($p->to_zone_id ?? 'null'));

        return view('pages.super-admin.delivery-zone-pricing', compact('city', 'zones', 'prices'));
    }

    public function store(Request $request, DeliveryCity $city)
    {
        $zones = DeliveryZone::where('delivery_city_id', $city->id)
            ->where('is_active', true)
            ->pluck('id');

        // prices[from_id][to_id] = montant (ou 'fallback' pour to_zone_id null)
        $pricesInput = $request->input('prices', []);

        foreach ($pricesInput as $fromId => $targets) {
            if (!$zones->contains((int) $fromId)) continue;

            foreach ($targets as $toKey => $priceStr) {
                $price = (int) preg_replace('/[^0-9]/', '', $priceStr);
                if ($price <= 0) continue;

                $toId = $toKey === 'fallback' ? null : (int) $toKey;
                if ($toId !== null && !$zones->contains($toId)) continue;

                DeliveryZonePrice::updateOrCreate(
                    ['from_zone_id' => (int) $fromId, 'to_zone_id' => $toId],
                    ['price_xof' => $price, 'is_active' => true]
                );
            }
        }

        return back()->with('success', 'Tarifs par quartier mis à jour.');
    }
}
```

- [ ] **Step 2 : Ajouter les routes**

Dans `routes/web.php`, dans le groupe super-admin, ajoute :

```php
Route::get('villes-livraison/{city}/tarifs', [\App\Http\Controllers\SuperAdmin\DeliveryZonePricingController::class, 'index'])->name('delivery.zone-pricing.index');
Route::post('villes-livraison/{city}/tarifs', [\App\Http\Controllers\SuperAdmin\DeliveryZonePricingController::class, 'store'])->name('delivery.zone-pricing.store');
```

- [ ] **Step 3 : Créer la vue (grille de prix)**

```blade
{{-- resources/views/pages/super-admin/delivery-zone-pricing.blade.php --}}
<x-layouts.admin-super title="Tarifs par quartier — {{ $city->name }}">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('super-admin.delivery.zone-pricing.index', $city) }}"
           class="text-sm font-medium" style="color:var(--sa-muted-fg);">
            ← Retour
        </a>
        <h1 class="text-xl font-bold" style="color:var(--sa-fg);">
            Tarifs livraison par quartier — {{ $city->name }}
        </h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 rounded-xl text-sm font-semibold" style="background:#f0fdf4;color:#166534;">
            ✅ {{ session('success') }}
        </div>
    @endif

    @if($zones->isEmpty())
        <div class="p-8 text-center rounded-2xl border" style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-muted-fg);">
            Aucun quartier défini pour cette ville. Créez des zones de livraison d'abord.
        </div>
    @else
    <form method="POST" action="{{ route('super-admin.delivery.zone-pricing.store', $city) }}">
        @csrf
        <div class="rounded-2xl border shadow-sm overflow-hidden mb-6" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="p-4 border-b" style="border-color:var(--sa-border);">
                <p class="text-sm font-semibold" style="color:var(--sa-fg);">
                    Grille des tarifs (FCFA) — Ligne = zone restaurant, Colonne = zone client
                </p>
                <p class="text-xs mt-1" style="color:var(--sa-muted-fg);">
                    La colonne "Hors-zone" s'applique quand le client n'est dans aucun quartier défini.
                    Laissez vide pour utiliser le calcul par kilomètre.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full" style="font-size:13px;">
                    <thead>
                        <tr style="background:var(--sa-muted);">
                            <th class="px-4 py-3 text-left font-semibold" style="color:var(--sa-muted-fg);min-width:140px;">
                                Restaurant ↓ Client →
                            </th>
                            @foreach($zones as $toZone)
                            <th class="px-3 py-3 text-center font-semibold" style="color:var(--sa-muted-fg);min-width:110px;">
                                {{ $toZone->name }}
                            </th>
                            @endforeach
                            <th class="px-3 py-3 text-center font-semibold" style="color:var(--sa-muted-fg);min-width:110px;">
                                Hors-zone
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y" style="divide-color:var(--sa-border);">
                        @foreach($zones as $fromZone)
                        <tr>
                            <td class="px-4 py-2 font-semibold" style="color:var(--sa-fg);background:var(--sa-muted);">
                                {{ $fromZone->name }}
                            </td>
                            @foreach($zones as $toZone)
                            @php
                                $key = $fromZone->id . '_' . $toZone->id;
                                $existing = $prices[$key] ?? null;
                            @endphp
                            <td class="px-3 py-2 text-center">
                                <input type="number"
                                       name="prices[{{ $fromZone->id }}][{{ $toZone->id }}]"
                                       value="{{ $existing?->price_xof ?? '' }}"
                                       min="0"
                                       step="50"
                                       placeholder="—"
                                       class="w-24 text-center rounded-lg border px-2 py-1.5 text-sm outline-none"
                                       style="background:var(--sa-bg);border-color:var(--sa-border);color:var(--sa-fg);">
                            </td>
                            @endforeach
                            {{-- Colonne fallback (to_zone_id NULL) --}}
                            @php
                                $fallbackKey = $fromZone->id . '_null';
                                $fallback = $prices[$fallbackKey] ?? null;
                            @endphp
                            <td class="px-3 py-2 text-center">
                                <input type="number"
                                       name="prices[{{ $fromZone->id }}][fallback]"
                                       value="{{ $fallback?->price_xof ?? '' }}"
                                       min="0"
                                       step="50"
                                       placeholder="—"
                                       class="w-24 text-center rounded-lg border px-2 py-1.5 text-sm outline-none"
                                       style="background:var(--sa-bg);border-color:var(--sa-border);color:var(--sa-fg);">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="px-6 py-3 rounded-xl text-sm font-black text-white transition-all hover:-translate-y-0.5"
                    style="background:#D45E0C">
                Enregistrer les tarifs
            </button>
        </div>
    </form>
    @endif
</x-layouts.admin-super>
```

- [ ] **Step 4 : Vérifier la syntaxe**

```bash
php -l app/Http/Controllers/SuperAdmin/DeliveryZonePricingController.php
php artisan view:clear
php artisan route:list | grep zone-pricing
```

Expected : 2 routes listées.

- [ ] **Step 5 : Commit**

```bash
git add app/Http/Controllers/SuperAdmin/DeliveryZonePricingController.php resources/views/pages/super-admin/delivery-zone-pricing.blade.php routes/web.php
git commit -m "feat(super-admin): add zone pricing matrix interface for delivery cities"
```

---

### Task 5 : Ajouter le lien depuis la page des villes de livraison Super Admin

**Files:**
- Modify: `resources/views/pages/super-admin/delivery-cities.blade.php` (ou équivalent)

**Interfaces:**
- Consumes: route `super-admin.delivery.zone-pricing.index`
- Produces: bouton "Tarifs quartiers" sur chaque ville

- [ ] **Step 1 : Trouver la vue des villes**

```bash
ls resources/views/pages/super-admin/ | grep -i deliv
grep -rn "DeliveryCity\|delivery.cities\|villes" resources/views/pages/super-admin/ | head -10
```

Identifie la vue qui liste les villes de livraison.

- [ ] **Step 2 : Ajouter le bouton de tarification**

Dans la liste des villes, pour chaque ville, ajoute un bouton :

```blade
<a href="{{ route('super-admin.delivery.zone-pricing.index', $city) }}"
   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors"
   style="background:rgba(212,94,12,.1);color:#D45E0C">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
    </svg>
    Tarifs quartiers
</a>
```

- [ ] **Step 3 : Vérifier visuellement**

```bash
php artisan view:clear
```

Ouvre `/admin/villes-livraison` et vérifie que le bouton apparaît sur chaque ville.

- [ ] **Step 4 : Commit**

```bash
git add resources/views/pages/super-admin/
git commit -m "feat(super-admin): add 'Tarifs quartiers' button on delivery cities list"
```

---

### Task 6 : Push et déploiement

- [ ] **Step 1 : Vérifier les commits**

```bash
git log --oneline -6
```

- [ ] **Step 2 : Push**

```bash
git push origin main
```

- [ ] **Step 3 : Déployer**

```bash
# Sur le serveur menupro.ci :
bash ~/deploy.sh
```

- [ ] **Step 4 : Saisir les tarifs Daloa**

1. Aller sur Super Admin → Villes de livraison → Daloa → Tarifs quartiers
2. Saisir la grille :
   - Tazibouo → Tazibouo = 500
   - Tazibouo → Corridor = 1500
   - Tazibouo → Hors-zone = 2000
   - (etc. pour tous les quartiers)
3. Enregistrer
4. Passer une commande de livraison test depuis le checkout → vérifier que le tarif affiché correspond

---

## Self-Review

### Spec coverage
- ✅ Table `delivery_zone_prices` → Task 1
- ✅ `to_zone_id IS NULL` = fallback → Task 1 + 2
- ✅ `DeliveryZonePrice::findPrice()` avec fallback → Task 2
- ✅ `DeliveryPricingService` cherche matrice en priorité, fallback km → Task 3
- ✅ Interface Super Admin grille from×to → Task 4
- ✅ Colonne "Hors-zone" dans la grille → Task 4
- ✅ Lien depuis page des villes → Task 5

### Type consistency
- `DeliveryZonePrice::findPrice(int, ?int): ?DeliveryZonePrice` → utilisé dans Task 3 ✓
- `DeliveryCity::haversineKm()` — méthode statique existante → utilisée dans Task 3 ✓
- `$zone->containsPoint(float, float): bool` — méthode existante sur DeliveryZone → utilisée dans Task 3 ✓
- `price_xof` = entier FCFA dans le modèle et la migration ✓

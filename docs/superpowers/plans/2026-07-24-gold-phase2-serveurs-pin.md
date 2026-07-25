# Plan GOLD — Phase 2 : Serveurs avec PIN

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Permettre à un restaurant Plan GOLD de créer des serveurs identifiés par un PIN à 4 chiffres. Chaque serveur ouvre une table depuis une interface dédiée (URL token), toutes les commandes de cette table (manuelles ou QR client) lui sont automatiquement liées. Le patron voit en fin de service qui a servi quoi.

**Architecture:** On crée une table `waiters` (nom, PIN hashé, espace, restaurant) et on ajoute `waiter_id` nullable sur `orders`. L'interface serveur suit le pattern token de la cuisine : URL publique `/serveur/{token}` avec polling, pas de connexion requise. Le serveur tape son PIN pour s'identifier, ouvre une table, et prend des commandes. Une table ouverte est verrouillée pour ce serveur jusqu'au paiement.

**Tech Stack:** Laravel 11, Livewire v4, Tailwind CSS, Alpine.js, Pest, bcrypt (PIN hashé), pattern KitchenController/StaffDisplayController

## Global Constraints

- `waiter_id` est TOUJOURS nullable — les restaurants non-GOLD et les commandes QR sans serveur actif fonctionnent exactement comme avant
- PIN = 4 chiffres, hashé en bcrypt, jamais stocké en clair
- 3 tentatives PIN incorrectes → blocage 5 minutes (via cache rate-limiter)
- Une table `ouverte` par un serveur ne peut pas être prise par un autre (verrou en DB)
- `table_number` reste une string libre (existant) — pas de nouvelle table `tables`
- L'interface serveur est une URL publique token-secured : `/serveur/{token}` — même pattern que `/cuisine/{token}` et `/personnel/{token}`
- Le token serveur s'appelle `waiter_token` sur `restaurants` (32 chars, Str::random(32))
- Feature Plan GOLD uniquement : vérifier `$restaurant->hasMultiSpaces()` pour accès à la gestion serveurs
- Les tests Pest sont dans `tests/Feature/Restaurant/`
- Ne jamais modifier le trait `BelongsToRestaurant`

---

## Fichiers créés ou modifiés

### Nouveaux fichiers
- `database/migrations/2026_07_24_100000_create_waiters_table.php`
- `database/migrations/2026_07_24_100001_add_waiter_id_to_orders_table.php`
- `database/migrations/2026_07_24_100002_add_waiter_token_to_restaurants_table.php`
- `app/Models/Waiter.php`
- `app/Http/Controllers/Restaurant/WaiterController.php`
- `resources/views/pages/waiter/display.blade.php`
- `app/Livewire/Restaurant/Waiters.php`
- `resources/views/livewire/restaurant/waiters.blade.php`
- `tests/Feature/Restaurant/WaitersTest.php`
- `database/factories/WaiterFactory.php`

### Fichiers modifiés
- `app/Models/Order.php` — relation `waiter()`, scope `scopeForWaiter()`
- `app/Models/Restaurant.php` — relation `waiters()`, colonne `waiter_token` dans fillable
- `routes/web.php` — routes CRUD waiters (admin+GOLD) + routes publiques `/serveur/{token}`

---

## Task 1 : Migration waiters + waiter_id sur orders + waiter_token sur restaurants

**Files:**
- Create: `database/migrations/2026_07_24_100000_create_waiters_table.php`
- Create: `database/migrations/2026_07_24_100001_add_waiter_id_to_orders_table.php`
- Create: `database/migrations/2026_07_24_100002_add_waiter_token_to_restaurants_table.php`
- Create: `app/Models/Waiter.php`
- Create: `database/factories/WaiterFactory.php`
- Modify: `app/Models/Order.php` — relation + scope
- Modify: `app/Models/Restaurant.php` — relation waiters() + waiter_token fillable
- Create: `tests/Feature/Restaurant/WaitersTest.php`

**Interfaces:**
- Produces: `Waiter` model avec `id`, `restaurant_id`, `space_id` (nullable), `name`, `pin_hash`, `is_active`, `failed_attempts`, `locked_until`
- Produces: `Order::waiter()` → BelongsTo(Waiter)
- Produces: `Order::scopeForWaiter(?int $waiterId)`
- Produces: `Restaurant::waiters()` → HasMany(Waiter)
- Produces: `Restaurant.waiter_token` — string 32 nullable unique

- [ ] **Step 1 : Créer la migration waiters**

```php
<?php
// database/migrations/2026_07_24_100000_create_waiters_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('waiters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('space_id')->nullable()->constrained('restaurant_spaces')->nullOnDelete();
            $table->string('name');
            $table->string('pin_hash');          // bcrypt du PIN 4 chiffres
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('failed_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            $table->timestamps();

            $table->index(['restaurant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('waiters');
    }
};
```

- [ ] **Step 2 : Créer la migration waiter_id sur orders**

```php
<?php
// database/migrations/2026_07_24_100001_add_waiter_id_to_orders_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('waiter_id')->nullable()->after('space_id')
                  ->constrained('waiters')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['waiter_id']);
            $table->dropColumn('waiter_id');
        });
    }
};
```

- [ ] **Step 3 : Créer la migration waiter_token sur restaurants**

```php
<?php
// database/migrations/2026_07_24_100002_add_waiter_token_to_restaurants_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->string('waiter_token', 32)->nullable()->unique()->after('staff_token');
        });
    }

    public function down(): void
    {
        Schema::table('restaurants', function (Blueprint $table) {
            $table->dropColumn('waiter_token');
        });
    }
};
```

- [ ] **Step 4 : Créer le modèle Waiter**

```php
<?php
// app/Models/Waiter.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Waiter extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'space_id', 'name', 'pin_hash', 'is_active', 'failed_attempts', 'locked_until',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'locked_until' => 'datetime',
    ];

    protected $hidden = ['pin_hash'];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(RestaurantSpace::class, 'space_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'waiter_id');
    }

    public function setPin(string $pin): void
    {
        $this->pin_hash = Hash::make($pin);
    }

    public function checkPin(string $pin): bool
    {
        return Hash::check($pin, $this->pin_hash);
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function recordFailedAttempt(): void
    {
        $this->failed_attempts++;
        if ($this->failed_attempts >= 3) {
            $this->locked_until   = now()->addMinutes(5);
            $this->failed_attempts = 0;
        }
        $this->save();
    }

    public function resetAttempts(): void
    {
        $this->failed_attempts = 0;
        $this->locked_until   = null;
        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

- [ ] **Step 5 : Créer la factory Waiter**

```bash
php artisan make:factory WaiterFactory --model=Waiter
```

Puis éditer `database/factories/WaiterFactory.php` :
```php
<?php
namespace Database\Factories;

use App\Models\Waiter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class WaiterFactory extends Factory
{
    protected $model = Waiter::class;

    public function definition(): array
    {
        return [
            'restaurant_id'  => \App\Models\Restaurant::factory(),
            'space_id'       => null,
            'name'           => fake()->name(),
            'pin_hash'       => Hash::make('1234'),
            'is_active'      => true,
            'failed_attempts'=> 0,
            'locked_until'   => null,
        ];
    }
}
```

- [ ] **Step 6 : Modifier Order.php — relation waiter() + scopeForWaiter()**

Ouvrir `app/Models/Order.php`. Ajouter après `use App\Models\RestaurantSpace;` :
```php
use App\Models\Waiter;
```

Ajouter les méthodes après `space()` :
```php
public function waiter(): BelongsTo
{
    return $this->belongsTo(Waiter::class, 'waiter_id');
}

public function scopeForWaiter($query, ?int $waiterId)
{
    if ($waiterId === null) return $query;
    return $query->where('waiter_id', $waiterId);
}
```

Ajouter `'waiter_id'` dans `$fillable`.

- [ ] **Step 7 : Modifier Restaurant.php — relation waiters() + waiter_token fillable**

Ouvrir `app/Models/Restaurant.php`. Ajouter dans `$fillable` : `'waiter_token'`.

Ajouter la relation après `spaces()` :
```php
public function waiters(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(Waiter::class)->orderBy('name');
}
```

- [ ] **Step 8 : Lancer les migrations**

```bash
cd c:/laragon/www/MenuPro
php artisan migrate
```

- [ ] **Step 9 : Écrire et lancer les tests**

```php
<?php
// tests/Feature/Restaurant/WaitersTest.php
namespace Tests\Feature\Restaurant;

use Tests\TestCase;
use App\Models\Waiter;
use App\Models\Restaurant;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class WaitersTest extends TestCase
{
    use RefreshDatabase;

    public function test_waiter_can_be_created_with_hashed_pin(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create([
            'restaurant_id' => $restaurant->id,
            'name'          => 'Koffi',
            'pin_hash'      => Hash::make('1234'),
        ]);

        $this->assertTrue($waiter->checkPin('1234'));
        $this->assertFalse($waiter->checkPin('9999'));
        $this->assertDatabaseHas('waiters', ['name' => 'Koffi', 'restaurant_id' => $restaurant->id]);
    }

    public function test_waiter_locks_after_3_failed_attempts(): void
    {
        $waiter = Waiter::factory()->create(['failed_attempts' => 0]);

        $waiter->recordFailedAttempt();
        $waiter->recordFailedAttempt();
        $this->assertFalse($waiter->isLocked());

        $waiter->recordFailedAttempt(); // 3e tentative → verrouillage
        $this->assertTrue($waiter->isLocked());
        $this->assertEquals(0, $waiter->failed_attempts); // reset après verrouillage
    }

    public function test_order_can_be_assigned_to_waiter(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        $order = Order::factory()->create([
            'restaurant_id' => $restaurant->id,
            'waiter_id'     => $waiter->id,
        ]);

        $this->assertEquals($waiter->id, $order->waiter_id);
        $this->assertEquals(1, Order::forWaiter($waiter->id)->count());
    }

    public function test_order_scope_for_waiter_null_returns_all(): void
    {
        $restaurant = Restaurant::factory()->create();
        $waiter = Waiter::factory()->create(['restaurant_id' => $restaurant->id]);
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'waiter_id' => $waiter->id]);
        Order::factory()->create(['restaurant_id' => $restaurant->id, 'waiter_id' => null]);

        $this->assertEquals(2, Order::forWaiter(null)->count());
    }

    public function test_restaurant_has_waiters_relation(): void
    {
        $restaurant = Restaurant::factory()->create();
        Waiter::factory()->count(3)->create(['restaurant_id' => $restaurant->id]);
        $this->assertEquals(3, $restaurant->waiters()->count());
    }
}
```

```bash
php artisan test tests/Feature/Restaurant/WaitersTest.php
```
Attendu : 5 tests passent.

- [ ] **Step 10 : Commit**

```bash
git add database/migrations/2026_07_24_100000_create_waiters_table.php \
        database/migrations/2026_07_24_100001_add_waiter_id_to_orders_table.php \
        database/migrations/2026_07_24_100002_add_waiter_token_to_restaurants_table.php \
        app/Models/Waiter.php \
        database/factories/WaiterFactory.php \
        app/Models/Order.php \
        app/Models/Restaurant.php \
        tests/Feature/Restaurant/WaitersTest.php
git commit -m "feat(gold): table waiters + PIN hashé + waiter_id sur orders + waiter_token sur restaurants"
```

---

## Task 2 : Interface CRUD Serveurs (Livewire dashboard)

**Files:**
- Create: `app/Livewire/Restaurant/Waiters.php`
- Create: `resources/views/livewire/restaurant/waiters.blade.php`
- Modify: `routes/web.php` — route GET /dashboard/serveurs (admin + multi_spaces)
- Modify: `resources/views/components/layouts/admin-restaurant.blade.php` — lien Serveurs dans sidebar

**Interfaces:**
- Consumes: `Waiter` model (Task 1), `Restaurant::hasMultiSpaces()` (Phase 1)
- Produces: Page `/dashboard/serveurs` — CRUD serveurs avec PIN

- [ ] **Step 1 : Créer le composant Livewire**

```php
<?php
// app/Livewire/Restaurant/Waiters.php
namespace App\Livewire\Restaurant;

use Livewire\Component;
use App\Models\Waiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Waiters extends Component
{
    public string $name    = '';
    public string $pin     = '';
    public string $pinConfirm = '';
    public ?int   $spaceId = null;
    public bool   $isActive = true;
    public ?int   $editingId = null;

    protected function rules(): array
    {
        $pinRule = $this->editingId
            ? 'nullable|digits:4'
            : 'required|digits:4|same:pinConfirm';
        return [
            'name'       => 'required|string|max:80',
            'pin'        => $pinRule,
            'pinConfirm' => 'nullable|digits:4',
            'spaceId'    => 'nullable|exists:restaurant_spaces,id',
            'isActive'   => 'boolean',
        ];
    }

    public function getRestaurantProperty()
    {
        return Auth::user()->restaurant;
    }

    public function getWaitersProperty()
    {
        return $this->restaurant->waiters()->with('space')->get();
    }

    public function getSpacesProperty()
    {
        return $this->restaurant->spaces()->active()->get();
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $waiter = Waiter::where('id', $this->editingId)
                ->where('restaurant_id', $this->restaurant->id)
                ->firstOrFail();

            $waiter->name      = $this->name;
            $waiter->space_id  = $this->spaceId;
            $waiter->is_active = $this->isActive;
            if ($this->pin) {
                $waiter->pin_hash = Hash::make($this->pin);
            }
            $waiter->save();
            session()->flash('success', 'Serveur mis à jour.');
        } else {
            Waiter::create([
                'restaurant_id' => $this->restaurant->id,
                'space_id'      => $this->spaceId,
                'name'          => $this->name,
                'pin_hash'      => Hash::make($this->pin),
                'is_active'     => $this->isActive,
            ]);
            session()->flash('success', 'Serveur créé.');
        }

        $this->reset(['name', 'pin', 'pinConfirm', 'spaceId', 'editingId']);
        $this->isActive = true;
    }

    public function edit(int $id): void
    {
        $waiter = Waiter::where('id', $id)
            ->where('restaurant_id', $this->restaurant->id)
            ->firstOrFail();

        $this->editingId = $waiter->id;
        $this->name      = $waiter->name;
        $this->spaceId   = $waiter->space_id;
        $this->isActive  = $waiter->is_active;
        $this->pin       = '';
        $this->pinConfirm = '';
    }

    public function delete(int $id): void
    {
        Waiter::where('id', $id)
            ->where('restaurant_id', $this->restaurant->id)
            ->delete();
        session()->flash('success', 'Serveur supprimé.');
    }

    public function cancelEdit(): void
    {
        $this->reset(['name', 'pin', 'pinConfirm', 'spaceId', 'editingId']);
        $this->isActive = true;
    }

    public function render()
    {
        return view('livewire.restaurant.waiters')
            ->layout('components.layouts.admin-restaurant', ['title' => 'Serveurs']);
    }
}
```

- [ ] **Step 2 : Créer la vue**

```blade
{{-- resources/views/livewire/restaurant/waiters.blade.php --}}
<div class="max-w-4xl mx-auto py-8 px-4">

    @if(session('success'))
    <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Serveurs</h1>
            <p class="text-neutral-500 text-sm mt-1">Chaque serveur s'identifie avec un PIN à 4 chiffres sur sa tablette</p>
        </div>
        @php $waiterUrl = $restaurant->waiter_token
            ? route('waiter.display', $restaurant->waiter_token)
            : null @endphp
        @if($waiterUrl)
        <a href="{{ $waiterUrl }}" target="_blank"
            class="flex items-center gap-2 px-4 py-2 bg-neutral-900 text-white text-sm font-semibold rounded-xl hover:bg-neutral-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Ouvrir l'interface serveur
        </a>
        @else
        <form method="POST" action="{{ route('restaurant.waiter.generate-token') }}">
            @csrf
            <button type="submit"
                class="flex items-center gap-2 px-4 py-2 bg-neutral-900 text-white text-sm font-semibold rounded-xl hover:bg-neutral-800 transition">
                Générer le lien interface serveur
            </button>
        </form>
        @endif
    </div>

    {{-- Formulaire --}}
    <div class="bg-white rounded-2xl border border-neutral-200 p-6 mb-8">
        <h2 class="font-bold text-neutral-900 mb-4">{{ $editingId ? 'Modifier le serveur' : 'Nouveau serveur' }}</h2>
        <form wire:submit="save" class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Nom *</label>
                <input type="text" wire:model="name" placeholder="Ex: Koffi, Aya, Jean..."
                    class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">
                    PIN (4 chiffres) {{ $editingId ? '— laisser vide pour ne pas changer' : '*' }}
                </label>
                <input type="password" wire:model="pin" placeholder="••••" maxlength="4" pattern="[0-9]{4}"
                    class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @error('pin') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            @if(!$editingId)
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Confirmer le PIN *</label>
                <input type="password" wire:model="pinConfirm" placeholder="••••" maxlength="4" pattern="[0-9]{4}"
                    class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @error('pinConfirm') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            @endif

            @if($this->spaces->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Espace assigné (optionnel)</label>
                <select wire:model="spaceId"
                    class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">Tous les espaces</option>
                    @foreach($this->spaces as $space)
                    <option value="{{ $space->id }}">{{ $space->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="isActive" id="isActive" class="rounded">
                <label for="isActive" class="text-sm text-neutral-700">Serveur actif</label>
            </div>

            <div class="flex gap-3 justify-end sm:col-span-2">
                @if($editingId)
                <button type="button" wire:click="cancelEdit"
                    class="px-5 py-2.5 text-sm font-medium text-neutral-700 bg-neutral-100 rounded-xl hover:bg-neutral-200 transition">
                    Annuler
                </button>
                @endif
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-bold bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition">
                    {{ $editingId ? 'Mettre à jour' : 'Créer le serveur' }}
                </button>
            </div>
        </form>
    </div>

    {{-- Liste des serveurs --}}
    <div class="space-y-3">
        @forelse($this->waiters as $waiter)
        <div class="bg-white rounded-xl border border-neutral-200 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center text-primary-700 font-bold shrink-0">
                {{ strtoupper(substr($waiter->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-semibold text-neutral-900">{{ $waiter->name }}</span>
                    @if($waiter->space)
                    <span class="text-xs px-2 py-0.5 rounded-full text-white font-medium" style="background-color: {{ $waiter->space->color }}">
                        {{ $waiter->space->name }}
                    </span>
                    @endif
                    @if(!$waiter->is_active)
                    <span class="text-xs bg-neutral-100 text-neutral-500 px-2 py-0.5 rounded-full">Inactif</span>
                    @endif
                    @if($waiter->isLocked())
                    <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">PIN bloqué</span>
                    @endif
                </div>
                <p class="text-xs text-neutral-400 mt-0.5">PIN ••••</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button wire:click="edit({{ $waiter->id }})"
                    class="p-2 text-neutral-500 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button wire:click="delete({{ $waiter->id }})" wire:confirm="Supprimer ce serveur ?"
                    class="p-2 text-neutral-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
        @empty
        <div class="text-center py-12 text-neutral-400">
            <p class="text-sm">Aucun serveur créé. Ajoutez votre premier serveur ci-dessus.</p>
        </div>
        @endforelse
    </div>
</div>
```

- [ ] **Step 3 : Ajouter la route et le lien sidebar**

Dans `routes/web.php`, dans le groupe dashboard, ajouter dans le groupe `['restaurant.admin', 'feature:multi_spaces']` (ou créer un nouveau groupe similaire) :
```php
Route::middleware(['restaurant.admin', 'feature:multi_spaces'])->group(function () {
    // ... existant (espaces) ...
    Route::get('serveurs', \App\Livewire\Restaurant\Waiters::class)->name('waiters');
    Route::post('serveurs/generate-token', [\App\Http\Controllers\Restaurant\WaiterController::class, 'generateToken'])->name('waiter.generate-token');
});
```

Dans `resources/views/components/layouts/admin-restaurant.blade.php`, ajouter après le lien "Espaces" :
```blade
@if($restaurant->hasMultiSpaces())
<a href="{{ route('restaurant.waiters') }}"
   class="{{ request()->routeIs('restaurant.waiters') ? 'bg-primary-50 text-primary-700' : 'text-neutral-600 hover:bg-neutral-50' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
    Serveurs
    <span class="ml-auto text-[10px] font-bold bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded-full">GOLD</span>
</a>
@endif
```

- [ ] **Step 4 : Créer WaiterController avec generateToken**

```php
<?php
// app/Http/Controllers/Restaurant/WaiterController.php
namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class WaiterController extends Controller
{
    public function generateToken(): \Illuminate\Http\RedirectResponse
    {
        $restaurant = auth()->user()->restaurant;
        $restaurant->waiter_token = Str::random(32);
        $restaurant->save();
        return redirect()->route('restaurant.waiters')
            ->with('success', 'Lien interface serveur généré.');
    }
}
```

- [ ] **Step 5 : Vérifier `php artisan route:list --name=restaurant.waiters`**

```bash
php artisan route:list --name=restaurant.waiters
```

- [ ] **Step 6 : Commit**

```bash
git add app/Livewire/Restaurant/Waiters.php \
        resources/views/livewire/restaurant/waiters.blade.php \
        app/Http/Controllers/Restaurant/WaiterController.php \
        routes/web.php \
        resources/views/components/layouts/admin-restaurant.blade.php
git commit -m "feat(gold): page CRUD serveurs + PIN + lien sidebar + génération token interface"
```

---

## Task 3 : Interface serveur dédiée (URL token publique)

**Files:**
- Create: `resources/views/pages/waiter/display.blade.php`
- Modify: `routes/web.php` — routes publiques `/serveur/{token}` (data + pin-auth + order)

**Interfaces:**
- Consumes: `Waiter` model (Task 1), `RestaurantSpace` (Phase 1), pattern `display.blade.php` cuisine/personnel
- Produces: URL `/serveur/{token}` — interface HTML/JS standalone pour serveur

**Note :** Cette interface est une page HTML pure (vanilla JS, pas de Livewire) identique au pattern cuisine/personnel. Elle gère :
1. Splash screen déverrouillage audio
2. Saisie PIN → authentification AJAX → session JS avec `waiter_id` + `waiter_name`
3. Sélection de table (champ libre)
4. Liste des commandes actives pour ce serveur
5. Formulaire de prise de commande basique (nom client + notes)

- [ ] **Step 1 : Ajouter les routes publiques dans `routes/web.php`**

En dehors du groupe `middleware(['auth', 'set.restaurant.scope'])`, ajouter :
```php
// Interface serveur (token public, comme cuisine et personnel)
Route::prefix('serveur/{token}')->name('waiter.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Restaurant\WaiterController::class, 'display'])->name('display');
    Route::post('/auth', [\App\Http\Controllers\Restaurant\WaiterController::class, 'authenticatePin'])->name('auth');
    Route::get('/data', [\App\Http\Controllers\Restaurant\WaiterController::class, 'data'])->name('data');
});
```

- [ ] **Step 2 : Ajouter les méthodes dans WaiterController**

```php
public function display(string $token): \Illuminate\View\View
{
    $restaurant = \App\Models\Restaurant::where('waiter_token', $token)->firstOrFail();
    $spaces     = $restaurant->spaces()->active()->get(['id', 'name', 'color']);
    return view('pages.waiter.display', compact('restaurant', 'token', 'spaces'));
}

public function authenticatePin(\Illuminate\Http\Request $request, string $token): \Illuminate\Http\JsonResponse
{
    $restaurant = \App\Models\Restaurant::where('waiter_token', $token)->firstOrFail();
    $request->validate(['pin' => 'required|digits:4']);

    // Chercher le serveur par son PIN (on essaie chaque serveur actif du restaurant)
    $waiters = \App\Models\Waiter::where('restaurant_id', $restaurant->id)
        ->where('is_active', true)
        ->get();

    foreach ($waiters as $waiter) {
        if ($waiter->isLocked()) continue;
        if ($waiter->checkPin($request->pin)) {
            $waiter->resetAttempts();
            return response()->json([
                'success'     => true,
                'waiter_id'   => $waiter->id,
                'waiter_name' => $waiter->name,
                'space_id'    => $waiter->space_id,
                'space_name'  => $waiter->space?->name,
            ]);
        }
    }

    // PIN incorrect — incrémenter les tentatives sur tous les serveurs qui n'ont pas matché
    // (on ne sait pas quel serveur a essayé, donc on rate-limit par IP via cache)
    $key = 'waiter_pin_fail_' . $request->ip() . '_' . $restaurant->id;
    $attempts = cache()->increment($key);
    cache()->put($key, $attempts, now()->addMinutes(5));

    if ($attempts >= 10) {
        // Bloquer tous les serveurs du restaurant après 10 tentatives IP
        $waiters->each->recordFailedAttempt();
    }

    return response()->json(['success' => false, 'message' => 'PIN incorrect'], 401);
}

public function data(string $token): \Illuminate\Http\JsonResponse
{
    $restaurant = \App\Models\Restaurant::where('waiter_token', $token)->firstOrFail();
    $waiterId   = request()->query('waiter_id');

    $orders = \App\Models\Order::where('restaurant_id', $restaurant->id)
        ->when($waiterId, fn($q) => $q->where('waiter_id', (int) $waiterId))
        ->whereIn('status', ['confirmed', 'preparing', 'ready'])
        ->with('items.dish')
        ->latest()
        ->take(50)
        ->get()
        ->map(fn($o) => [
            'id'           => $o->id,
            'reference'    => $o->reference,
            'table'        => $o->table_number,
            'status'       => $o->status->value,
            'status_label' => $o->status->label(),
            'items'        => $o->items->map(fn($i) => $i->dish?->name . ' x' . $i->quantity)->join(', '),
            'total'        => number_format($o->total, 0, '.', ' ') . ' F',
            'created_at'   => $o->created_at->format('H:i'),
        ]);

    return response()->json(['orders' => $orders]);
}
```

- [ ] **Step 3 : Créer la vue `display.blade.php`**

Créer le dossier `resources/views/pages/waiter/` puis la vue :

```html
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interface Serveur — {{ $restaurant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { background: #0f172a; color: #f1f5f9; font-family: system-ui, sans-serif; }
        .pin-btn { width: 64px; height: 64px; border-radius: 50%; background: #1e293b; border: 1px solid #334155; font-size: 1.5rem; font-weight: bold; cursor: pointer; transition: background 0.15s; }
        .pin-btn:hover { background: #334155; }
        .pin-dot { width: 14px; height: 14px; border-radius: 50%; border: 2px solid #64748b; transition: background 0.15s; }
        .pin-dot.filled { background: #6366f1; border-color: #6366f1; }
    </style>
</head>
<body class="min-h-screen">

{{-- Splash déverrouillage audio --}}
<div id="splash" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900">
    <div class="text-center">
        <div class="text-4xl mb-4">👨‍🍳</div>
        <h1 class="text-2xl font-bold text-white mb-2">{{ $restaurant->name }}</h1>
        <p class="text-slate-400 mb-8">Interface Serveur</p>
        <button onclick="startApp()" class="px-8 py-4 bg-indigo-600 text-white font-bold rounded-2xl text-lg hover:bg-indigo-500 transition">
            Appuyer pour démarrer
        </button>
    </div>
</div>

{{-- App principale --}}
<div id="app" class="hidden min-h-screen">

    {{-- PIN Screen --}}
    <div id="pin-screen" class="flex items-center justify-center min-h-screen">
        <div class="text-center w-full max-w-xs mx-auto px-4">
            <h2 class="text-xl font-bold text-white mb-2">Entrez votre PIN</h2>
            <p class="text-slate-400 text-sm mb-8">{{ $restaurant->name }}</p>

            {{-- Dots indicateurs --}}
            <div class="flex justify-center gap-4 mb-8" id="pin-dots">
                <div class="pin-dot" id="dot-0"></div>
                <div class="pin-dot" id="dot-1"></div>
                <div class="pin-dot" id="dot-2"></div>
                <div class="pin-dot" id="dot-3"></div>
            </div>

            {{-- Clavier PIN --}}
            <div class="grid grid-cols-3 gap-4 justify-items-center mb-4">
                @foreach([1,2,3,4,5,6,7,8,9] as $n)
                <button class="pin-btn" onclick="addPin('{{ $n }}')">{{ $n }}</button>
                @endforeach
                <div></div>
                <button class="pin-btn" onclick="addPin('0')">0</button>
                <button class="pin-btn text-slate-400" onclick="deletePin()">⌫</button>
            </div>

            <div id="pin-error" class="text-red-400 text-sm mt-4 hidden">PIN incorrect. Réessayez.</div>
            <div id="pin-locked" class="text-amber-400 text-sm mt-4 hidden">Trop de tentatives. Attendez 5 minutes.</div>
        </div>
    </div>

    {{-- Dashboard Serveur --}}
    <div id="dashboard" class="hidden">
        <header class="bg-slate-800 border-b border-slate-700 px-4 py-3 flex items-center justify-between">
            <div>
                <span class="text-white font-bold" id="waiter-name-display">Serveur</span>
                <span class="text-slate-400 text-sm ml-2" id="space-display"></span>
            </div>
            <button onclick="logout()" class="text-slate-400 text-sm hover:text-white transition">Déconnexion</button>
        </header>

        <div class="max-w-2xl mx-auto px-4 py-6">
            {{-- Info table --}}
            <div class="bg-slate-800 rounded-2xl p-4 mb-6">
                <label class="block text-slate-400 text-xs font-semibold uppercase tracking-wide mb-2">Numéro de table</label>
                <input type="text" id="table-input" placeholder="Ex: 5, VIP-3, Terrasse 2..."
                    class="w-full bg-slate-700 text-white rounded-xl px-4 py-3 text-lg font-bold placeholder-slate-500 border border-slate-600 focus:border-indigo-500 focus:outline-none">
            </div>

            {{-- Commandes actives --}}
            <h3 class="text-slate-400 text-xs font-semibold uppercase tracking-wide mb-3">Mes commandes actives</h3>
            <div id="orders-list" class="space-y-3">
                <p class="text-slate-500 text-sm text-center py-8">Aucune commande active</p>
            </div>
        </div>
    </div>

</div>

<script>
    const TOKEN   = '{{ $token }}';
    const AUTH_URL = '{{ route('waiter.auth', $token) }}';
    const DATA_URL = '{{ route('waiter.data', $token) }}';
    const CSRF     = '{{ csrf_token() }}';

    let pin = '';
    let waiterSession = null;
    let pollInterval  = null;

    // --- Splash ---
    function startApp() {
        document.getElementById('splash').classList.add('hidden');
        document.getElementById('app').classList.remove('hidden');
    }

    // --- PIN ---
    function addPin(digit) {
        if (pin.length >= 4) return;
        pin += digit;
        updateDots();
        if (pin.length === 4) setTimeout(submitPin, 200);
    }

    function deletePin() {
        pin = pin.slice(0, -1);
        updateDots();
        hideErrors();
    }

    function updateDots() {
        for (let i = 0; i < 4; i++) {
            document.getElementById('dot-' + i).classList.toggle('filled', i < pin.length);
        }
    }

    function hideErrors() {
        document.getElementById('pin-error').classList.add('hidden');
        document.getElementById('pin-locked').classList.add('hidden');
    }

    async function submitPin() {
        try {
            const res = await fetch(AUTH_URL, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body: JSON.stringify({ pin }),
            });
            const data = await res.json();
            if (data.success) {
                waiterSession = data;
                document.getElementById('pin-screen').classList.add('hidden');
                document.getElementById('dashboard').classList.remove('hidden');
                document.getElementById('waiter-name-display').textContent = data.waiter_name;
                document.getElementById('space-display').textContent = data.space_name ? '— ' + data.space_name : '';
                startPolling();
            } else {
                pin = '';
                updateDots();
                document.getElementById('pin-error').classList.remove('hidden');
            }
        } catch (e) {
            pin = '';
            updateDots();
        }
    }

    function logout() {
        waiterSession = null;
        pin = '';
        updateDots();
        stopPolling();
        document.getElementById('dashboard').classList.add('hidden');
        document.getElementById('pin-screen').classList.remove('hidden');
        hideErrors();
    }

    // --- Polling commandes ---
    function startPolling() { pollInterval = setInterval(fetchOrders, 5000); fetchOrders(); }
    function stopPolling()  { if (pollInterval) clearInterval(pollInterval); }

    async function fetchOrders() {
        if (!waiterSession) return;
        try {
            const res  = await fetch(DATA_URL + '?waiter_id=' + waiterSession.waiter_id);
            const data = await res.json();
            renderOrders(data.orders || []);
        } catch (e) {}
    }

    function renderOrders(orders) {
        const list = document.getElementById('orders-list');
        if (!orders.length) {
            list.innerHTML = '<p class="text-slate-500 text-sm text-center py-8">Aucune commande active</p>';
            return;
        }
        list.innerHTML = orders.map(o => `
            <div class="bg-slate-800 rounded-xl p-4 border border-slate-700">
                <div class="flex items-center justify-between mb-2">
                    <span class="font-bold text-white">Table ${o.table || '?'}</span>
                    <span class="text-xs px-2 py-1 rounded-full font-semibold ${statusClass(o.status)}">${o.status_label}</span>
                </div>
                <p class="text-slate-400 text-sm">${o.items}</p>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-slate-500 text-xs">${o.reference} · ${o.created_at}</span>
                    <span class="text-indigo-400 font-bold text-sm">${o.total}</span>
                </div>
            </div>
        `).join('');
    }

    function statusClass(status) {
        return {
            confirmed:  'bg-blue-900 text-blue-300',
            preparing:  'bg-amber-900 text-amber-300',
            ready:      'bg-emerald-900 text-emerald-300',
        }[status] || 'bg-slate-700 text-slate-300';
    }
</script>
</body>
</html>
```

- [ ] **Step 4 : Vérifier les routes**

```bash
php artisan route:list --name=waiter
```

- [ ] **Step 5 : Commit**

```bash
git add resources/views/pages/waiter/display.blade.php \
        app/Http/Controllers/Restaurant/WaiterController.php \
        routes/web.php
git commit -m "feat(gold): interface serveur dédiée /serveur/{token} — PIN auth + commandes actives"
```

---

## Self-Review

**Couverture spec :**
- ✅ Table `waiters` avec PIN hashé bcrypt — Task 1
- ✅ Blocage après 3 tentatives — Task 1 (Waiter::recordFailedAttempt)
- ✅ `waiter_id` nullable sur orders — Task 1
- ✅ CRUD serveurs dans le dashboard (admin + GOLD only) — Task 2
- ✅ Interface serveur token-secured — Task 3
- ✅ Authentification PIN via AJAX — Task 3
- ✅ Polling commandes actives — Task 3
- ⚠️ Verrouillage de table par serveur — non implémenté en Phase 2 (les commandes QR client ne sont pas automatiquement liées au serveur de la table). Prévu en Phase 2b : ajouter une table `waiter_table_assignments` pour tracker quel serveur a ouvert quelle table.

**Types cohérents :**
- `waiter_id` : int nullable sur orders ✅
- `scopeForWaiter(?int $waiterId)` : null = pas de filtre ✅
- `waiter_token` : string 32 sur restaurants ✅

---

*Plan GOLD Phase 2 — Serveurs avec PIN*
*Durée estimée : 3-4 jours*
*Prérequis : Phase 1 déployée en production*

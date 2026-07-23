# Plan GOLD — Phase 1 : Fondation Multi-Espaces

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Permettre à un restaurant Plan GOLD de créer des espaces autonomes (VIP, VVIP, salle...) avec menu, stock et commandes séparés — sans casser les restaurants mono-espace existants.

**Architecture:** On ajoute une table `restaurant_spaces` et une colonne `space_id` nullable sur `dishes`, `ingredients`, `orders` et `categories`. Les restaurants non-GOLD voient `space_id = null` partout — aucun changement pour eux. Le GOLD active un filtre par espace dans le dashboard via un sélecteur global.

**Tech Stack:** Laravel 11, Livewire v4, Tailwind CSS, MySQL 8, Alpine.js

## Global Constraints

- `space_id` est toujours **nullable** — les restaurants non-GOLD fonctionnent exactement comme avant
- La feature flag s'appelle `multi_spaces` — vérifier avec `$restaurant->hasFeature('multi_spaces')`
- Plan GOLD = slug `gold`, prix 85000, sort_order 4
- Toutes les migrations utilisent le format `YYYY_MM_DD_HHMMSS_description.php`
- Ne jamais modifier `BelongsToRestaurant` trait — le scope `restaurant_id` reste inchangé
- Laravel conventions : models en singular PascalCase, tables en plural snake_case
- Les tests Pest sont dans `tests/Feature/` et `tests/Unit/`

---

## Fichiers créés ou modifiés

### Nouveaux fichiers
- `database/migrations/2026_07_23_100000_create_restaurant_spaces_table.php`
- `database/migrations/2026_07_23_100001_add_space_id_to_dishes_table.php`
- `database/migrations/2026_07_23_100002_add_space_id_to_ingredients_table.php`
- `database/migrations/2026_07_23_100003_add_space_id_to_orders_table.php`
- `database/migrations/2026_07_23_100004_add_multi_spaces_to_plans_table.php`
- `app/Models/RestaurantSpace.php`
- `app/Livewire/Restaurant/Spaces.php`
- `resources/views/livewire/restaurant/spaces.blade.php`
- `resources/views/components/space-selector.blade.php`
- `tests/Feature/Restaurant/SpacesTest.php`

### Fichiers modifiés
- `database/seeders/PlanSeeder.php` — ajout Plan GOLD
- `app/Models/Dish.php` — relation `space()`, scope `scopeForSpace()`
- `app/Models/Ingredient.php` — relation `space()`, scope `scopeForSpace()`
- `app/Models/Order.php` — relation `space()`, scope `scopeForSpace()`
- `app/Models/Restaurant.php` — relation `spaces()`, méthode `hasMultiSpaces()`
- `routes/web.php` — route CRUD espaces + middleware `feature:multi_spaces`
- `resources/views/components/layouts/admin-restaurant.blade.php` — lien Espaces dans sidebar + sélecteur d'espace actif
- `app/Http/Middleware/SetRestaurantScope.php` — partage `$currentSpace` avec les vues

---

## Task 1 : Plan GOLD dans le seeder + feature flag DB

**Files:**
- Modify: `database/seeders/PlanSeeder.php`
- Create: `database/migrations/2026_07_23_100004_add_multi_spaces_to_plans_table.php`

**Interfaces:**
- Produces: colonne `has_multi_spaces` (boolean) sur table `plans`, Plan GOLD slug=`gold` en DB

- [ ] **Step 1 : Créer la migration**

```php
<?php
// database/migrations/2026_07_23_100004_add_multi_spaces_to_plans_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('has_multi_spaces')->default(false)->after('has_hotel_rooms');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('has_multi_spaces');
        });
    }
};
```

- [ ] **Step 2 : Vérifier le PlanSeeder existant**

Lire `database/seeders/PlanSeeder.php` pour voir la structure exacte des plans existants, puis ajouter le Plan GOLD. La structure de chaque plan contient : `name`, `slug`, `price`, `sort_order`, `description`, et des colonnes `has_*` pour les features.

- [ ] **Step 3 : Ajouter `has_multi_spaces` à tous les plans existants + Plan GOLD**

Dans `PlanSeeder.php`, ajouter `has_multi_spaces => false` à chaque plan existant (Stand, Essentiel, Pro, Business) et ajouter le Plan GOLD. Lire d'abord le fichier pour connaître la structure exacte, puis faire l'edit. Le Plan GOLD doit avoir les mêmes features que Business + `has_multi_spaces = true`.

Exemple de structure à ajouter (adapter selon la structure existante) :
```php
[
    'name'             => 'Gold',
    'slug'             => 'gold',
    'price'            => 85000,
    'sort_order'       => 4,
    'description'      => 'Complexes multi-espaces, maquis VIP, hôtels',
    'has_multi_spaces' => true,
    // Copier toutes les autres colonnes has_* à true (même que Business ou plus)
],
```

- [ ] **Step 4 : Vérifier que `hasFeature('multi_spaces')` fonctionne**

Lire `app/Models/Restaurant.php` méthode `hasFeature()` et `app/Models/Plan.php` pour comprendre comment les features sont vérifiées. Si la méthode lit directement les colonnes `has_*`, aucun changement n'est nécessaire — `hasFeature('multi_spaces')` fonctionnera automatiquement grâce à la nouvelle colonne.

- [ ] **Step 5 : Lancer la migration et re-seeder les plans**

```bash
cd c:/laragon/www/MenuPro
php artisan migrate
php artisan db:seed --class=PlanSeeder
```

Vérifier en console que le plan GOLD est présent :
```bash
php artisan tinker --execute="echo App\Models\Plan::where('slug','gold')->value('name');"
```
Résultat attendu : `Gold`

- [ ] **Step 6 : Écrire le test**

```php
<?php
// tests/Feature/Restaurant/SpacesTest.php
namespace Tests\Feature\Restaurant;

use Tests\TestCase;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SpacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_gold_plan_has_multi_spaces_feature(): void
    {
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $plan = Plan::where('slug', 'gold')->firstOrFail();
        $this->assertTrue((bool) $plan->has_multi_spaces);
    }

    public function test_non_gold_plans_do_not_have_multi_spaces(): void
    {
        $this->seed(\Database\Seeders\PlanSeeder::class);
        $plans = Plan::where('slug', '!=', 'gold')->get();
        foreach ($plans as $plan) {
            $this->assertFalse((bool) $plan->has_multi_spaces, "Plan {$plan->slug} should not have multi_spaces");
        }
    }
}
```

- [ ] **Step 7 : Lancer le test**

```bash
php artisan test tests/Feature/Restaurant/SpacesTest.php
```
Attendu : 2 tests passent.

- [ ] **Step 8 : Commit**

```bash
git add database/migrations/2026_07_23_100004_add_multi_spaces_to_plans_table.php database/seeders/PlanSeeder.php tests/Feature/Restaurant/SpacesTest.php
git commit -m "feat(gold): plan GOLD 85000F + colonne has_multi_spaces sur plans"
```

---

## Task 2 : Table `restaurant_spaces` et modèle

**Files:**
- Create: `database/migrations/2026_07_23_100000_create_restaurant_spaces_table.php`
- Create: `app/Models/RestaurantSpace.php`
- Modify: `app/Models/Restaurant.php` — ajouter relation `spaces()` et méthode `hasMultiSpaces()`

**Interfaces:**
- Produces: `RestaurantSpace` model avec `id`, `restaurant_id`, `name`, `color`, `description`, `is_active`
- Produces: `Restaurant::spaces()` → HasMany(RestaurantSpace)
- Produces: `Restaurant::hasMultiSpaces()` → bool

- [ ] **Step 1 : Créer la migration**

```php
<?php
// database/migrations/2026_07_23_100000_create_restaurant_spaces_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('restaurant_spaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('restaurant_id')->constrained()->cascadeOnDelete();
            $table->string('name');           // "VIP", "VVIP", "Salle", "Bar", "Terrasse"...
            $table->string('color', 7)->default('#6366f1'); // Hex color pour code couleur cuisine
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['restaurant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restaurant_spaces');
    }
};
```

- [ ] **Step 2 : Créer le modèle**

```php
<?php
// app/Models/RestaurantSpace.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantSpace extends Model
{
    protected $fillable = [
        'restaurant_id', 'name', 'color', 'description', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class, 'space_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'space_id');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'space_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

- [ ] **Step 3 : Ajouter la relation dans Restaurant.php**

Ouvrir `app/Models/Restaurant.php`, trouver les méthodes de relations (chercher `HasMany` ou `public function dishes()`), et ajouter :

```php
public function spaces(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(RestaurantSpace::class)->orderBy('sort_order');
}

public function hasMultiSpaces(): bool
{
    return $this->hasFeature('multi_spaces');
}
```

- [ ] **Step 4 : Lancer la migration**

```bash
php artisan migrate
```

- [ ] **Step 5 : Ajouter les tests dans SpacesTest.php**

Ajouter dans `tests/Feature/Restaurant/SpacesTest.php` :

```php
public function test_restaurant_can_have_spaces(): void
{
    $this->seed(\Database\Seeders\PlanSeeder::class);
    $restaurant = \App\Models\Restaurant::factory()->create([
        'current_plan_id' => \App\Models\Plan::where('slug', 'gold')->value('id'),
    ]);

    $space = \App\Models\RestaurantSpace::create([
        'restaurant_id' => $restaurant->id,
        'name'          => 'VIP',
        'color'         => '#f59e0b',
        'is_active'     => true,
        'sort_order'    => 1,
    ]);

    $this->assertDatabaseHas('restaurant_spaces', ['name' => 'VIP', 'restaurant_id' => $restaurant->id]);
    $this->assertEquals(1, $restaurant->spaces()->count());
}

public function test_restaurant_has_multi_spaces_returns_true_for_gold(): void
{
    $this->seed(\Database\Seeders\PlanSeeder::class);
    $restaurant = \App\Models\Restaurant::factory()->create([
        'current_plan_id' => \App\Models\Plan::where('slug', 'gold')->value('id'),
    ]);
    $this->assertTrue($restaurant->hasMultiSpaces());
}
```

- [ ] **Step 6 : Lancer les tests**

```bash
php artisan test tests/Feature/Restaurant/SpacesTest.php
```
Attendu : 4 tests passent.

- [ ] **Step 7 : Commit**

```bash
git add database/migrations/2026_07_23_100000_create_restaurant_spaces_table.php app/Models/RestaurantSpace.php app/Models/Restaurant.php tests/Feature/Restaurant/SpacesTest.php
git commit -m "feat(gold): table restaurant_spaces + modèle RestaurantSpace + relation Restaurant::spaces()"
```

---

## Task 3 : Colonnes `space_id` sur dishes, ingredients, orders

**Files:**
- Create: `database/migrations/2026_07_23_100001_add_space_id_to_dishes_table.php`
- Create: `database/migrations/2026_07_23_100002_add_space_id_to_ingredients_table.php`
- Create: `database/migrations/2026_07_23_100003_add_space_id_to_orders_table.php`
- Modify: `app/Models/Dish.php` — relation + scope
- Modify: `app/Models/Ingredient.php` — relation + scope
- Modify: `app/Models/Order.php` — relation + scope

**Interfaces:**
- Consumes: `restaurant_spaces.id` (Task 2)
- Produces: `Dish::scopeForSpace($spaceId)`, `Ingredient::scopeForSpace($spaceId)`, `Order::scopeForSpace($spaceId)`

- [ ] **Step 1 : Créer les 3 migrations**

```php
<?php
// database/migrations/2026_07_23_100001_add_space_id_to_dishes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->foreignId('space_id')->nullable()->after('restaurant_id')
                  ->constrained('restaurant_spaces')->nullOnDelete();
            $table->index('space_id');
        });
    }
    public function down(): void
    {
        Schema::table('dishes', function (Blueprint $table) {
            $table->dropForeign(['space_id']);
            $table->dropColumn('space_id');
        });
    }
};
```

```php
<?php
// database/migrations/2026_07_23_100002_add_space_id_to_ingredients_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->foreignId('space_id')->nullable()->after('restaurant_id')
                  ->constrained('restaurant_spaces')->nullOnDelete();
            $table->index('space_id');
        });
    }
    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropForeign(['space_id']);
            $table->dropColumn('space_id');
        });
    }
};
```

```php
<?php
// database/migrations/2026_07_23_100003_add_space_id_to_orders_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('space_id')->nullable()->after('restaurant_id')
                  ->constrained('restaurant_spaces')->nullOnDelete();
            $table->index('space_id');
        });
    }
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['space_id']);
            $table->dropColumn('space_id');
        });
    }
};
```

- [ ] **Step 2 : Ajouter `space_id` dans les fillable et relations des modèles**

Dans `app/Models/Dish.php`, ajouter `'space_id'` au `$fillable` et ajouter :
```php
public function space(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(\App\Models\RestaurantSpace::class, 'space_id');
}

public function scopeForSpace($query, ?int $spaceId)
{
    if ($spaceId === null) return $query;
    return $query->where('space_id', $spaceId);
}
```

Dans `app/Models/Ingredient.php`, ajouter `'space_id'` au `$fillable` et le même pattern :
```php
public function space(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(\App\Models\RestaurantSpace::class, 'space_id');
}

public function scopeForSpace($query, ?int $spaceId)
{
    if ($spaceId === null) return $query;
    return $query->where('space_id', $spaceId);
}
```

Dans `app/Models/Order.php`, ajouter `'space_id'` au `$fillable` et le même pattern :
```php
public function space(): \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    return $this->belongsTo(\App\Models\RestaurantSpace::class, 'space_id');
}

public function scopeForSpace($query, ?int $spaceId)
{
    if ($spaceId === null) return $query;
    return $query->where('space_id', $spaceId);
}
```

- [ ] **Step 3 : Lancer les migrations**

```bash
php artisan migrate
```

- [ ] **Step 4 : Ajouter les tests dans SpacesTest.php**

```php
public function test_dish_can_be_assigned_to_space(): void
{
    $this->seed(\Database\Seeders\PlanSeeder::class);
    $restaurant = \App\Models\Restaurant::factory()->create();
    $space = \App\Models\RestaurantSpace::factory()->create(['restaurant_id' => $restaurant->id]);
    $dish = \App\Models\Dish::factory()->create([
        'restaurant_id' => $restaurant->id,
        'space_id'      => $space->id,
    ]);
    $this->assertEquals($space->id, $dish->space_id);
    $this->assertEquals(1, \App\Models\Dish::forSpace($space->id)->count());
}

public function test_order_scope_for_space_filters_correctly(): void
{
    $this->seed(\Database\Seeders\PlanSeeder::class);
    $restaurant = \App\Models\Restaurant::factory()->create();
    $space1 = \App\Models\RestaurantSpace::factory()->create(['restaurant_id' => $restaurant->id]);
    $space2 = \App\Models\RestaurantSpace::factory()->create(['restaurant_id' => $restaurant->id]);

    \App\Models\Order::factory()->create(['restaurant_id' => $restaurant->id, 'space_id' => $space1->id]);
    \App\Models\Order::factory()->create(['restaurant_id' => $restaurant->id, 'space_id' => $space2->id]);
    \App\Models\Order::factory()->create(['restaurant_id' => $restaurant->id, 'space_id' => null]);

    $this->assertEquals(1, \App\Models\Order::forSpace($space1->id)->count());
    $this->assertEquals(3, \App\Models\Order::forSpace(null)->count()); // null = pas de filtre
}
```

- [ ] **Step 5 : Créer les factories pour RestaurantSpace (si elles n'existent pas)**

```bash
php artisan make:factory RestaurantSpaceFactory --model=RestaurantSpace
```

Puis éditer `database/factories/RestaurantSpaceFactory.php` :
```php
public function definition(): array
{
    return [
        'restaurant_id' => \App\Models\Restaurant::factory(),
        'name'          => fake()->randomElement(['VIP', 'VVIP', 'Salle', 'Bar', 'Terrasse']),
        'color'         => fake()->hexColor(),
        'is_active'     => true,
        'sort_order'    => fake()->numberBetween(0, 10),
    ];
}
```

- [ ] **Step 6 : Lancer les tests**

```bash
php artisan test tests/Feature/Restaurant/SpacesTest.php
```
Attendu : 6 tests passent.

- [ ] **Step 7 : Commit**

```bash
git add database/migrations/2026_07_23_100001_add_space_id_to_dishes_table.php database/migrations/2026_07_23_100002_add_space_id_to_ingredients_table.php database/migrations/2026_07_23_100003_add_space_id_to_orders_table.php app/Models/Dish.php app/Models/Ingredient.php app/Models/Order.php database/factories/RestaurantSpaceFactory.php tests/Feature/Restaurant/SpacesTest.php
git commit -m "feat(gold): space_id nullable sur dishes/ingredients/orders + scopes ForSpace"
```

---

## Task 4 : Interface CRUD Espaces (Livewire)

**Files:**
- Create: `app/Livewire/Restaurant/Spaces.php`
- Create: `resources/views/livewire/restaurant/spaces.blade.php`
- Modify: `routes/web.php` — ajouter route `GET /dashboard/espaces`
- Modify: `resources/views/components/layouts/admin-restaurant.blade.php` — lien sidebar

**Interfaces:**
- Consumes: `RestaurantSpace` model (Task 2), `Restaurant::hasMultiSpaces()` (Task 2)
- Produces: Page `/dashboard/espaces` accessible uniquement Plan GOLD

- [ ] **Step 1 : Créer le composant Livewire**

```php
<?php
// app/Livewire/Restaurant/Spaces.php
namespace App\Livewire\Restaurant;

use Livewire\Component;
use App\Models\RestaurantSpace;
use Illuminate\Support\Facades\Auth;

class Spaces extends Component
{
    public string $name = '';
    public string $color = '#6366f1';
    public string $description = '';
    public bool $is_active = true;
    public ?int $editingId = null;

    protected function rules(): array
    {
        return [
            'name'        => 'required|string|max:50',
            'color'       => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
            'description' => 'nullable|string|max:200',
            'is_active'   => 'boolean',
        ];
    }

    public function getRestaurantProperty()
    {
        return Auth::user()->restaurant;
    }

    public function getSpacesProperty()
    {
        return $this->restaurant->spaces()->orderBy('sort_order')->get();
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name'          => $this->name,
            'color'         => $this->color,
            'description'   => $this->description,
            'is_active'     => $this->is_active,
        ];

        if ($this->editingId) {
            RestaurantSpace::where('id', $this->editingId)
                ->where('restaurant_id', $this->restaurant->id)
                ->update($data);
            session()->flash('success', 'Espace mis à jour.');
        } else {
            $data['restaurant_id'] = $this->restaurant->id;
            $data['sort_order']    = $this->restaurant->spaces()->max('sort_order') + 1;
            RestaurantSpace::create($data);
            session()->flash('success', 'Espace créé.');
        }

        $this->reset(['name', 'color', 'description', 'is_active', 'editingId']);
        $this->color = '#6366f1';
    }

    public function edit(int $id): void
    {
        $space = RestaurantSpace::where('id', $id)
            ->where('restaurant_id', $this->restaurant->id)
            ->firstOrFail();

        $this->editingId   = $space->id;
        $this->name        = $space->name;
        $this->color       = $space->color;
        $this->description = $space->description ?? '';
        $this->is_active   = $space->is_active;
    }

    public function delete(int $id): void
    {
        RestaurantSpace::where('id', $id)
            ->where('restaurant_id', $this->restaurant->id)
            ->delete();
        session()->flash('success', 'Espace supprimé.');
    }

    public function cancelEdit(): void
    {
        $this->reset(['name', 'color', 'description', 'is_active', 'editingId']);
        $this->color = '#6366f1';
    }

    public function render()
    {
        return view('livewire.restaurant.spaces')
            ->layout('components.layouts.admin-restaurant', ['title' => 'Espaces']);
    }
}
```

- [ ] **Step 2 : Créer la vue**

```blade
{{-- resources/views/livewire/restaurant/spaces.blade.php --}}
<div class="max-w-4xl mx-auto py-8 px-4">

    @if(session('success'))
    <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Espaces</h1>
            <p class="text-neutral-500 text-sm mt-1">Gérez les espaces de votre établissement (VIP, Salle, Bar...)</p>
        </div>
    </div>

    {{-- Formulaire création/édition --}}
    <div class="bg-white rounded-2xl border border-neutral-200 p-6 mb-8">
        <h2 class="font-bold text-neutral-900 mb-4">
            {{ $editingId ? 'Modifier l\'espace' : 'Nouvel espace' }}
        </h2>
        <form wire:submit="save" class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Nom *</label>
                <input type="text" wire:model="name" placeholder="Ex: VIP, Salle, Bar, Terrasse..."
                    class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Couleur (code cuisine)</label>
                <div class="flex items-center gap-3">
                    <input type="color" wire:model="color"
                        class="h-10 w-16 rounded-lg border border-neutral-200 cursor-pointer">
                    <span class="text-sm text-neutral-500">{{ $color }}</span>
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-neutral-700 mb-1">Description (optionnel)</label>
                <input type="text" wire:model="description" placeholder="Description courte..."
                    class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="is_active" id="is_active" class="rounded">
                <label for="is_active" class="text-sm text-neutral-700">Espace actif</label>
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
                    {{ $editingId ? 'Mettre à jour' : 'Créer l\'espace' }}
                </button>
            </div>
        </form>
    </div>

    {{-- Liste des espaces --}}
    <div class="space-y-3">
        @forelse($this->spaces as $space)
        <div class="bg-white rounded-xl border border-neutral-200 px-5 py-4 flex items-center gap-4">
            <div class="w-4 h-4 rounded-full shrink-0" style="background-color: {{ $space->color }}"></div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-neutral-900">{{ $space->name }}</span>
                    @if(!$space->is_active)
                    <span class="text-xs bg-neutral-100 text-neutral-500 px-2 py-0.5 rounded-full">Inactif</span>
                    @endif
                </div>
                @if($space->description)
                <p class="text-sm text-neutral-500 mt-0.5">{{ $space->description }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button wire:click="edit({{ $space->id }})"
                    class="p-2 text-neutral-500 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button wire:click="delete({{ $space->id }})" wire:confirm="Supprimer cet espace ?"
                    class="p-2 text-neutral-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
        @empty
        <div class="text-center py-12 text-neutral-400">
            <p class="text-sm">Aucun espace créé. Ajoutez votre premier espace ci-dessus.</p>
        </div>
        @endforelse
    </div>
</div>
```

- [ ] **Step 3 : Ajouter la route dans `routes/web.php`**

Trouver le groupe dashboard avec `->middleware(['auth', 'set.restaurant.scope'])` et ajouter dans la section admin (après `->middleware('restaurant.admin')`) :

```php
Route::middleware(['feature:multi_spaces'])->group(function () {
    Route::get('espaces', \App\Livewire\Restaurant\Spaces::class)->name('spaces');
});
```

- [ ] **Step 4 : Ajouter le lien dans la sidebar**

Dans `resources/views/components/layouts/admin-restaurant.blade.php`, trouver la section "Gestion" et ajouter le lien Espaces **conditionnel** (visible seulement si Plan GOLD) :

```blade
@if($restaurant->hasMultiSpaces())
<a href="{{ route('restaurant.spaces') }}"
   class="{{ request()->routeIs('restaurant.spaces') ? 'bg-primary-50 text-primary-700' : 'text-neutral-600 hover:bg-neutral-50' }} flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
    Espaces
    <span class="ml-auto text-[10px] font-bold bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded-full">GOLD</span>
</a>
@endif
```

- [ ] **Step 5 : Tester manuellement**

1. Assigner le Plan GOLD à un restaurant de test en tinker :
```bash
php artisan tinker --execute="App\Models\Restaurant::first()->update(['current_plan_id' => App\Models\Plan::where('slug','gold')->value('id')]);"
```
2. Se connecter au dashboard → vérifier que le lien "Espaces" apparaît dans la sidebar avec badge GOLD
3. Créer 3 espaces (VIP, VVIP, Salle) depuis l'interface
4. Vérifier qu'ils apparaissent en DB : `php artisan tinker --execute="dd(App\Models\RestaurantSpace::all()->pluck('name'));"`

- [ ] **Step 6 : Commit**

```bash
git add app/Livewire/Restaurant/Spaces.php resources/views/livewire/restaurant/spaces.blade.php routes/web.php resources/views/components/layouts/admin-restaurant.blade.php
git commit -m "feat(gold): page CRUD espaces + lien sidebar conditionnel Plan GOLD"
```

---

## Task 5 : Sélecteur d'espace actif dans le dashboard

**Files:**
- Create: `resources/views/components/space-selector.blade.php`
- Modify: `app/Http/Middleware/SetRestaurantScope.php` — partager `$currentSpaceId` en session
- Modify: `resources/views/components/layouts/admin-restaurant.blade.php` — intégrer le sélecteur

**Interfaces:**
- Consumes: `RestaurantSpace` model (Task 2), `Restaurant::hasMultiSpaces()` (Task 2)
- Produces: `session('current_space_id')` — int|null utilisable dans les Livewire components pour filtrer

- [ ] **Step 1 : Lire `SetRestaurantScope` middleware**

Lire `app/Http/Middleware/SetRestaurantScope.php` pour comprendre comment il fonctionne, puis ajouter le partage du `current_space_id` avec les vues.

- [ ] **Step 2 : Modifier `SetRestaurantScope` pour partager l'espace actif**

Après la ligne qui partage `$restaurant` avec les vues (chercher `view()->share`), ajouter :

```php
// Partager l'espace actif (null = tous les espaces)
$currentSpaceId = session('current_space_id');
// Valider que l'espace appartient bien au restaurant
if ($currentSpaceId) {
    $spaceExists = \App\Models\RestaurantSpace::where('id', $currentSpaceId)
        ->where('restaurant_id', $restaurant->id)
        ->exists();
    if (!$spaceExists) {
        $currentSpaceId = null;
        session()->forget('current_space_id');
    }
}
view()->share('currentSpaceId', $currentSpaceId);
```

- [ ] **Step 3 : Créer le composant sélecteur**

```blade
{{-- resources/views/components/space-selector.blade.php --}}
@if($restaurant->hasMultiSpaces() && $restaurant->spaces()->active()->count() > 0)
<div x-data="{ open: false }" class="relative">
    <button @click="open = !open"
        class="flex items-center gap-2 px-3 py-2 bg-white border border-neutral-200 rounded-xl text-sm font-medium text-neutral-700 hover:border-neutral-300 transition shadow-sm">
        @if($currentSpaceId)
            @php $activeSpace = $restaurant->spaces->find($currentSpaceId) @endphp
            <span class="w-3 h-3 rounded-full" style="background-color: {{ $activeSpace?->color ?? '#6366f1' }}"></span>
            <span>{{ $activeSpace?->name ?? 'Espace' }}</span>
        @else
            <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            <span>Tous les espaces</span>
        @endif
        <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>

    <div x-show="open" @click.outside="open = false" x-transition
        class="absolute top-full left-0 mt-1 bg-white border border-neutral-200 rounded-xl shadow-lg z-50 min-w-[180px] py-1">
        {{-- Tous les espaces --}}
        <form method="POST" action="{{ route('restaurant.spaces.select') }}">
            @csrf
            <input type="hidden" name="space_id" value="">
            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm hover:bg-neutral-50 flex items-center gap-2 {{ !$currentSpaceId ? 'font-semibold text-primary-600' : 'text-neutral-700' }}">
                <svg class="w-3.5 h-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Tous les espaces
            </button>
        </form>
        @foreach($restaurant->spaces()->active()->get() as $space)
        <form method="POST" action="{{ route('restaurant.spaces.select') }}">
            @csrf
            <input type="hidden" name="space_id" value="{{ $space->id }}">
            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm hover:bg-neutral-50 flex items-center gap-2 {{ $currentSpaceId == $space->id ? 'font-semibold text-primary-600' : 'text-neutral-700' }}">
                <span class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $space->color }}"></span>
                {{ $space->name }}
            </button>
        </form>
        @endforeach
    </div>
</div>
@endif
```

- [ ] **Step 4 : Ajouter la route de sélection dans `routes/web.php`**

Dans le groupe dashboard :
```php
Route::post('espaces/select', function (\Illuminate\Http\Request $request) {
    $spaceId = $request->input('space_id') ?: null;
    session(['current_space_id' => $spaceId]);
    return back();
})->name('spaces.select');
```

- [ ] **Step 5 : Intégrer le sélecteur dans la navbar du layout**

Dans `resources/views/components/layouts/admin-restaurant.blade.php`, trouver la barre de navigation supérieure (header/topbar) et ajouter `<x-space-selector />` à côté du nom du restaurant ou dans la zone des actions.

- [ ] **Step 6 : Tester manuellement**

1. Avec un restaurant Plan GOLD ayant 3 espaces créés (Task 4)
2. Cliquer sur le sélecteur → dropdown s'ouvre avec les 3 espaces + "Tous les espaces"
3. Sélectionner "VIP" → `session('current_space_id')` = id du VIP, la page se recharge
4. Vérifier dans tinker : `php artisan tinker` puis vérifier la session

- [ ] **Step 7 : Commit**

```bash
git add resources/views/components/space-selector.blade.php app/Http/Middleware/SetRestaurantScope.php resources/views/components/layouts/admin-restaurant.blade.php routes/web.php
git commit -m "feat(gold): sélecteur espace actif dans le dashboard — filtre session current_space_id"
```

---

## Self-Review

**Couverture spec :**
- ✅ Multi-espaces illimités — Task 2 (restaurant_spaces)
- ✅ Feature flag `multi_spaces` — Task 1 (plan GOLD)
- ✅ Stock séparé par espace — Task 3 (space_id sur ingredients)
- ✅ Commandes par espace — Task 3 (space_id sur orders)
- ✅ Menu par espace — Task 3 (space_id sur dishes)
- ✅ CRUD espaces — Task 4
- ✅ Sélecteur espace actif — Task 5
- ⚠️ Les Livewire components existants (Dishes, Orders, IngredientStock) doivent utiliser `session('current_space_id')` pour filtrer — **prévu en Phase 1b** (hors scope de ce plan, à faire après validation)

**Types cohérents :**
- `space_id` : int nullable partout ✅
- `scopeForSpace(?int $spaceId)` : signature identique sur les 3 modèles ✅
- `session('current_space_id')` : clé de session identique dans middleware et route ✅

**Placeholders :** aucun TBD ni TODO dans le plan ✅

---

*Plan GOLD Phase 1 — Fondation Multi-Espaces*
*Durée estimée : 2-3 jours de développement*
*Prérequis : Phase 1 terminée avant de démarrer Phase 2 (Serveurs PIN)*

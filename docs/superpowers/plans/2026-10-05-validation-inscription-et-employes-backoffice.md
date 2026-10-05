# Validation inscriptions & Employés back-office — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Bloquer l'accès des restaurants non validés et permettre au super admin de créer des employés back-office à accès limité par section.

**Architecture:** Partie A rebranche l'inscription sur le workflow d'approbation existant (statut PENDING, essai démarré à l'approbation, dashboard bloqué). Partie B ajoute une colonne `admin_permissions` (JSON) sur `users`, un middleware résolveur unique qui mappe le nom de route admin vers une clé de section et vérifie la permission, et une UI de création d'employés par cases à cocher. Aucune librairie externe.

**Tech Stack:** Laravel 12, PHPUnit 11, Blade, Alpine.js, MySQL.

**Spec:** `docs/superpowers/specs/2026-10-05-validation-inscription-et-employes-backoffice-design.md`

## Global Constraints

- Aucune solution payante : pas d'OTP/SMS, pas d'email payant, pas de librairie externe.
- Un seul niveau d'action par section (section accordée = voir + gérer).
- `admin_permissions = null` ⇒ admin complet (accès total) ; tableau de clés ⇒ employé restreint.
- Clés de sections délégables : `restaurants`, `orders`, `deliveries`, `subscriptions`, `crm`, `announcements`, `customers`, `finance`.
- Sections réservées aux admins complets (jamais délégables) : gestion utilisateurs/employés, paramètres système, config paiement, Jeko KYC, dashboard, statistiques, activité.
- Le correctif login `is_active` est **déjà présent** dans `LoginRequest::authenticate` — ne rien y ajouter.

## Review Focus

- **Employé sans aucune section** : à la connexion, redirection sûre (ne pas boucler ni 403 l'accueil) → testé en Task 7.
- **Escalade de privilèges** : un employé ne doit pas pouvoir atteindre `utilisateurs.*` ni se créer/modifier des permissions → testé en Task 4 et Task 6.
- **Route admin non mappée** (nouvelle zone future) : le résolveur doit refuser par défaut (réservé admin complet), jamais autoriser par défaut → testé en Task 3.
- **Restaurant PENDING tentant une route publique** : comportement « page publique bloquée » conservé, inchangé → couvert par l'existant, vérifié en Task 9.
- **Impersonation** : un super admin qui « impersonate » un resto PENDING doit pouvoir le gérer (bypass super admin conservé dans `EnsureRestaurantActive`) → vérifié en Task 9.

---

# PARTIE A — Validation des inscriptions

### Task 1: Écran d'attente + route

**Files:**
- Modify: `routes/web.php` (ajouter la route `restaurant.pending` dans le groupe dashboard `auth`)
- Create: `resources/views/pages/restaurant/pending.blade.php`
- Test: `tests/Feature/Restaurant/PendingScreenTest.php`

**Interfaces:**
- Produces: route nommée `restaurant.pending` (GET), vue `pages.restaurant.pending`.

- [ ] **Step 1: Écrire le test qui échoue**

```php
<?php
namespace Tests\Feature\Restaurant;

use App\Enums\RestaurantStatus;
use App\Enums\UserRole;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingScreenTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_screen_is_reachable_by_authenticated_owner(): void
    {
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::PENDING]);
        $user = User::factory()->create(['restaurant_id' => $restaurant->id]);
        $user->role = UserRole::RESTAURANT_ADMIN; $user->save();

        $this->actingAs($user)
            ->get(route('restaurant.pending'))
            ->assertOk()
            ->assertSee('en attente de validation', false);
    }
}
```

- [ ] **Step 2: Lancer le test → échec** `php artisan test --filter=PendingScreenTest` — Expected: FAIL (route inexistante).

- [ ] **Step 3: Créer la vue** `resources/views/pages/restaurant/pending.blade.php`

```blade
<x-layouts.public>
    <div class="min-h-[70vh] flex items-center justify-center px-4">
        <div class="max-w-md text-center bg-white rounded-2xl border border-neutral-200 p-8 shadow-sm">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-amber-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="text-xl font-bold text-neutral-900 mb-2">Inscription reçue</h1>
            <p class="text-sm text-neutral-600 mb-6">
                Votre restaurant est <strong>en attente de validation</strong> par notre équipe.
                Vous recevrez une notification dès qu'il sera activé. Votre essai gratuit démarrera à ce moment-là.
            </p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-sm font-semibold text-primary-600 hover:text-primary-700">Se déconnecter</button>
            </form>
        </div>
    </div>
</x-layouts.public>
```

- [ ] **Step 4: Ajouter la route** dans `routes/web.php`, dans le groupe des routes restaurant authentifiées (préfixe `dashboard`, name `restaurant.`), à côté de `restaurant.dashboard` :

```php
Route::get('en-attente', fn () => view('pages.restaurant.pending'))->name('pending');
```

> Vérifier le nom réel du groupe : la route finale doit s'appeler `restaurant.pending`. Si le groupe n'applique pas déjà `auth`, ajouter `->middleware('auth')` sur cette route.

- [ ] **Step 5: Lancer le test → succès** `php artisan test --filter=PendingScreenTest` — Expected: PASS.

- [ ] **Step 6: Commit** `git add -A && git commit -m "feat(onboarding): écran d'attente de validation restaurant"`

---

### Task 2: Inscription en PENDING + essai gelé + blocage dashboard

**Files:**
- Modify: `app/Http/Controllers/Auth/RegisterController.php` (bloc création restaurant ~l.145-160, abonnement ~l.198-209, redirection ~l.218-223)
- Modify: `app/Http/Middleware/EnsureRestaurantActive.php` (branche dashboard, section « Allow access for pending restaurants »)
- Modify: `app/Http/Controllers/SuperAdmin/RestaurantController.php` (`approve()` ~l.150)
- Test: `tests/Feature/Auth/RegistrationApprovalTest.php`

**Interfaces:**
- Consumes: route `restaurant.pending` (Task 1).
- Produces: un restaurant auto-inscrit a `status = PENDING` et un abonnement d'essai avec `starts_at = null`, `ends_at = null` ; `approve()` démarre l'essai.

- [ ] **Step 1: Écrire les tests qui échouent**

```php
<?php
namespace Tests\Feature\Auth;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function registrationPayload(): array
    {
        return [
            'name' => 'Koné',
            'restaurant_name' => 'Chez Koné',
            'phone' => '0700000000',
            'email' => 'kone@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'restaurant_city' => 'Bouaké',
        ];
    }

    public function test_registration_creates_pending_restaurant_with_frozen_trial(): void
    {
        Plan::factory()->create(['slug' => 'stand', 'price' => 5000]);

        $this->post(route('register.post'), $this->registrationPayload())
            ->assertRedirect(route('restaurant.pending'));

        $restaurant = Restaurant::firstOrFail();
        $this->assertSame(RestaurantStatus::PENDING, $restaurant->status);

        $sub = Subscription::firstOrFail();
        $this->assertNull($sub->starts_at);
        $this->assertNull($sub->ends_at);
    }

    public function test_pending_restaurant_cannot_reach_dashboard(): void
    {
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::PENDING]);
        $user = User::factory()->restaurantAdmin($restaurant)->create();

        $this->actingAs($user)
            ->get(route('restaurant.dashboard'))
            ->assertRedirect(route('restaurant.pending'));
    }

    public function test_approval_activates_and_starts_trial(): void
    {
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::PENDING]);
        $sub = Subscription::factory()->create([
            'restaurant_id' => $restaurant->id,
            'status' => SubscriptionStatus::TRIAL,
            'is_trial' => true,
            'trial_days' => 7,
            'starts_at' => null,
            'ends_at' => null,
        ]);
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->post(route('super-admin.restaurants.approve', $restaurant))
            ->assertRedirect();

        $restaurant->refresh();
        $sub->refresh();
        $this->assertSame(RestaurantStatus::ACTIVE, $restaurant->status);
        $this->assertNotNull($sub->starts_at);
        $this->assertTrue($sub->ends_at->greaterThan(now()->addDays(6)));
    }
}
```

> Si les factory helpers `restaurantAdmin()` / `superAdmin()` n'existent pas sur `UserFactory`, les ajouter (states) dans cette étape — c'est du scaffolding de test, il appartient à cette tâche.

- [ ] **Step 2: Lancer → échec** `php artisan test --filter=RegistrationApprovalTest` — Expected: FAIL.

- [ ] **Step 3: RegisterController — restaurant en PENDING.** Dans `store()`, remplacer `'status' => RestaurantStatus::ACTIVE,` par `'status' => RestaurantStatus::PENDING,`. Retirer `'subscription_ends_at' => $trialEndsAt,` (ou le mettre à `null`) de la création du restaurant.

- [ ] **Step 4: RegisterController — essai gelé.** Dans la création du `Subscription` d'essai, mettre `'starts_at' => null,` et `'ends_at' => null,` (au lieu de `now()` / `$trialEndsAt`). Conserver `is_trial => true`, `status => SubscriptionStatus::TRIAL`, `trial_days => $trialDays`.

- [ ] **Step 5: RegisterController — redirection.** Remplacer la redirection finale `redirect()->route('restaurant.dashboard')...` par :

```php
return redirect()->route('restaurant.pending');
```

- [ ] **Step 6: Middleware — bloquer le dashboard PENDING.** Dans `EnsureRestaurantActive::handle`, branche `isDashboardRoute`, remplacer le commentaire « Allow access for pending restaurants » et le passage par :

```php
if ($restaurant->status === RestaurantStatus::PENDING
    && !$request->routeIs('restaurant.pending')) {
    return redirect()->route('restaurant.pending');
}
```
(placer AVANT les checks SUSPENDED/EXPIRED existants ; conserver ceux-ci).

- [ ] **Step 7: approve() — démarrer l'essai.** Dans `SuperAdmin\RestaurantController::approve()`, après `$restaurant->validate();`, ajouter la gestion de l'essai gelé :

```php
$trial = $restaurant->subscriptions()
    ->where('is_trial', true)
    ->whereNull('starts_at')
    ->latest()
    ->first();

if ($trial) {
    $trial->update([
        'starts_at' => now(),
        'ends_at'   => now()->addDays($trial->trial_days ?? 7),
    ]);
    $restaurant->update(['subscription_ends_at' => $trial->ends_at]);
}
```
(conserver le bloc existant qui active un abonnement payant `PENDING`).

- [ ] **Step 8: Lancer → succès** `php artisan test --filter=RegistrationApprovalTest` — Expected: PASS.

- [ ] **Step 9: Commit** `git add -A && git commit -m "feat(onboarding): inscription en PENDING, essai démarré à l'approbation, dashboard bloqué"`

---

# PARTIE B — Employés back-office

### Task 3: Colonne `admin_permissions` + helpers User

**Files:**
- Create: `database/migrations/2026_10_05_000001_add_admin_permissions_to_users_table.php`
- Modify: `app/Models/User.php` (casts + helpers)
- Create: `app/Support/AdminSections.php` (liste des clés + mapping route→section)
- Test: `tests/Unit/AdminPermissionsTest.php`

**Interfaces:**
- Produces:
  - `User::isFullAdmin(): bool`
  - `User::canAdminSection(string $section): bool`
  - `AdminSections::DELEGABLE` (array des 8 clés), `AdminSections::sectionForRouteName(?string $routeName): ?string` (retourne la clé de section, ou `null` si réservé/inconnu → traité comme réservé).

- [ ] **Step 1: Écrire les tests unitaires qui échouent**

```php
<?php
namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\AdminSections;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_admin_has_null_permissions_and_can_access_everything(): void
    {
        $u = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $this->assertTrue($u->isFullAdmin());
        $this->assertTrue($u->canAdminSection('restaurants'));
        $this->assertTrue($u->canAdminSection('finance'));
    }

    public function test_restricted_employee_only_accesses_granted_sections(): void
    {
        $u = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $this->assertFalse($u->isFullAdmin());
        $this->assertTrue($u->canAdminSection('restaurants'));
        $this->assertFalse($u->canAdminSection('finance'));
    }

    public function test_route_name_maps_to_section(): void
    {
        $this->assertSame('restaurants', AdminSections::sectionForRouteName('super-admin.restaurants.index'));
        $this->assertSame('deliveries', AdminSections::sectionForRouteName('super-admin.drivers.show'));
        $this->assertSame('finance', AdminSections::sectionForRouteName('super-admin.finances.index'));
        // Réservé / inconnu → null (traité comme réservé aux admins complets)
        $this->assertNull(AdminSections::sectionForRouteName('super-admin.utilisateurs.index'));
        $this->assertNull(AdminSections::sectionForRouteName('super-admin.parametres.whatever'));
        $this->assertNull(AdminSections::sectionForRouteName('super-admin.zone-future.index'));
    }
}
```

- [ ] **Step 2: Lancer → échec** `php artisan test --filter=AdminPermissionsTest` — Expected: FAIL.

- [ ] **Step 3: Migration**

```php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('admin_permissions')->nullable()->after('is_active');
        });
    }
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('admin_permissions');
        });
    }
};
```

- [ ] **Step 4: `app/Support/AdminSections.php`**

```php
<?php
namespace App\Support;

class AdminSections
{
    /** Clés de sections délégables (cases à cocher). */
    public const DELEGABLE = [
        'restaurants', 'orders', 'deliveries', 'subscriptions',
        'crm', 'announcements', 'customers', 'finance',
    ];

    public const LABELS = [
        'restaurants'   => 'Restaurants (valider inscriptions)',
        'orders'        => 'Commandes',
        'deliveries'    => 'Livraison & Livreurs',
        'subscriptions' => 'Abonnements & Plans',
        'crm'           => 'CRM & Ambassadeurs',
        'announcements' => 'Annonces & Promos',
        'customers'     => 'Clients',
        'finance'       => 'Finances (transactions, reversements)',
    ];

    /** Premier segment du nom de route admin → clé de section. Absent ⇒ réservé admin complet. */
    private const ROUTE_MAP = [
        'restaurants'         => 'restaurants',
        'orders'              => 'orders',
        'deliveries'          => 'deliveries',
        'drivers'             => 'deliveries',
        'delivery-cities'     => 'deliveries',
        'delivery-zones'      => 'deliveries',
        'delivery'            => 'deliveries',
        'subscriptions'       => 'subscriptions',
        'plans'               => 'subscriptions',
        'commando'            => 'crm',
        'announcements'       => 'announcements',
        'promo-banners'       => 'announcements',
        'push'                => 'announcements',
        'customers'           => 'customers',
        'transactions'        => 'finance',
        'finances'            => 'finance',
    ];

    public static function sectionForRouteName(?string $routeName): ?string
    {
        if (!$routeName || !str_starts_with($routeName, 'super-admin.')) {
            return null;
        }
        $rest = substr($routeName, strlen('super-admin.'));
        $segment = explode('.', $rest)[0];
        return self::ROUTE_MAP[$segment] ?? null;
    }
}
```

- [ ] **Step 5: `User.php` — cast + helpers.** Ajouter `'admin_permissions' => 'array',` au tableau `casts()`. Ajouter :

```php
public function isFullAdmin(): bool
{
    return $this->isSuperAdmin() && $this->admin_permissions === null;
}

public function canAdminSection(string $section): bool
{
    if (!$this->isSuperAdmin()) {
        return false;
    }
    if ($this->admin_permissions === null) {
        return true; // admin complet
    }
    return in_array($section, $this->admin_permissions, true);
}
```

- [ ] **Step 6: Lancer → succès** `php artisan test --filter=AdminPermissionsTest` — Expected: PASS.

- [ ] **Step 7: Commit** `git add -A && git commit -m "feat(rbac): colonne admin_permissions + helpers User + mapping sections"`

---

### Task 4: Middleware `EnsureAdminSection` sur le groupe admin

**Files:**
- Create: `app/Http/Middleware/EnsureAdminSection.php`
- Modify: `bootstrap/app.php` (alias `admin.section`)
- Modify: `routes/web.php` (ajouter `admin.section` au groupe `super.admin`, l.365)
- Test: `tests/Feature/Admin/AdminSectionAccessTest.php`

**Interfaces:**
- Consumes: `AdminSections::sectionForRouteName()`, `User::isFullAdmin()`, `User::canAdminSection()` (Task 3).
- Produces: alias middleware `admin.section` appliqué à tout le groupe admin.

- [ ] **Step 1: Écrire les tests qui échouent**

```php
<?php
namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSectionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_admin_accesses_any_section(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $this->actingAs($admin)->get(route('super-admin.restaurants.index'))->assertOk();
        $this->actingAs($admin)->get(route('super-admin.finances.index'))->assertOk();
    }

    public function test_restricted_employee_allowed_only_on_granted_section(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $this->actingAs($emp)->get(route('super-admin.restaurants.index'))->assertOk();
        $this->actingAs($emp)->get(route('super-admin.finances.index'))->assertForbidden();
    }

    public function test_restricted_employee_cannot_reach_reserved_section(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $this->actingAs($emp)->get(route('super-admin.utilisateurs.index'))->assertForbidden();
        $this->actingAs($emp)->get(route('super-admin.settings'))->assertForbidden();
    }
}
```

- [ ] **Step 2: Lancer → échec** `php artisan test --filter=AdminSectionAccessTest` — Expected: FAIL.

- [ ] **Step 3: Middleware**

```php
<?php
namespace App\Http\Middleware;

use App\Support\AdminSections;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSection
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Admin complet : accès total (le bypass historique).
        if ($user && $user->isFullAdmin()) {
            return $next($request);
        }

        $section = AdminSections::sectionForRouteName($request->route()?->getName());

        // Section réservée (null) ou non accordée → 403.
        if ($section === null || !$user?->canAdminSection($section)) {
            abort(403, 'Accès réservé.');
        }

        return $next($request);
    }
}
```

- [ ] **Step 4: Alias.** Dans `bootstrap/app.php`, bloc `$middleware->alias([...])`, ajouter :

```php
'admin.section' => \App\Http\Middleware\EnsureAdminSection::class,
```

- [ ] **Step 5: Appliquer au groupe.** Dans `routes/web.php` l.365, ajouter `admin.section` après `super.admin` :

```php
->middleware(['auth', 'super.admin', 'admin.section'])
```

- [ ] **Step 6: Lancer → succès** `php artisan test --filter=AdminSectionAccessTest` — Expected: PASS.

- [ ] **Step 7: Commit** `git add -A && git commit -m "feat(rbac): middleware admin.section + garde par nom de route"`

---

### Task 5: `Gate::before` restreint aux admins complets

**Files:**
- Modify: `app/Providers/AppServiceProvider.php` (`Gate::before`, ~l.92)
- Test: `tests/Feature/Admin/GateBypassTest.php`

**Interfaces:**
- Consumes: `User::isFullAdmin()` (Task 3).

- [ ] **Step 1: Écrire le test qui échoue**

```php
<?php
namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class GateBypassTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_admin_bypasses_all_gates(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $this->assertTrue(Gate::forUser($admin)->allows('any-undefined-ability'));
    }

    public function test_restricted_employee_does_not_bypass_gates(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $this->assertFalse(Gate::forUser($emp)->allows('any-undefined-ability'));
    }
}
```

- [ ] **Step 2: Lancer → échec** `php artisan test --filter=GateBypassTest` — Expected: FAIL (le 2e test échoue : l'employé bypasse encore).

- [ ] **Step 3: Modifier `Gate::before`.** Remplacer `if ($user->isSuperAdmin()) {` par `if ($user->isFullAdmin()) {` dans le callback `Gate::before`.

- [ ] **Step 4: Lancer → succès** `php artisan test --filter=GateBypassTest` — Expected: PASS.

- [ ] **Step 5: Commit** `git add -A && git commit -m "fix(rbac): Gate::before ne bypasse que les admins complets"`

---

### Task 6: Création d'employés (`UserController::store`)

**Files:**
- Modify: `app/Http/Controllers/SuperAdmin/UserController.php` (`store()`)
- Test: `tests/Feature/Admin/EmployeeCreationTest.php`

**Interfaces:**
- Consumes: `AdminSections::DELEGABLE` (Task 3), `admin.section` (réserve déjà `utilisateurs.*` aux admins complets — Task 4).
- Produces: un employé = `role = SUPER_ADMIN` + `admin_permissions = [clés validées]`.

- [ ] **Step 1: Écrire les tests qui échouent**

```php
<?php
namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_admin_creates_restricted_employee(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);

        $this->actingAs($admin)->post(route('super-admin.utilisateurs.store'), [
            'name' => 'Awa', 'email' => 'awa@example.com', 'phone' => '0701010101',
            'password' => 'password123', 'account_type' => 'employee',
            'admin_permissions' => ['restaurants', 'orders'],
        ])->assertRedirect();

        $awa = User::where('email', 'awa@example.com')->firstOrFail();
        $this->assertSame(UserRole::SUPER_ADMIN, $awa->role);
        $this->assertEqualsCanonicalizing(['restaurants', 'orders'], $awa->admin_permissions);
        $this->assertFalse($awa->isFullAdmin());
    }

    public function test_invalid_section_is_rejected(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $this->actingAs($admin)->post(route('super-admin.utilisateurs.store'), [
            'name' => 'X', 'email' => 'x@example.com', 'password' => 'password123',
            'account_type' => 'employee', 'admin_permissions' => ['payment-settings'],
        ])->assertSessionHasErrors('admin_permissions.0');
    }

    public function test_restricted_employee_cannot_create_users(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $this->actingAs($emp)->post(route('super-admin.utilisateurs.store'), [
            'name' => 'Y', 'email' => 'y@example.com', 'password' => 'password123',
            'account_type' => 'full',
        ])->assertForbidden();
    }
}
```

- [ ] **Step 2: Lancer → échec** `php artisan test --filter=EmployeeCreationTest` — Expected: FAIL.

- [ ] **Step 3: Réécrire `store()`**

```php
public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:users'],
        'phone' => ['nullable', 'string', 'max:20'],
        'password' => ['required', 'string', 'min:8'],
        'account_type' => ['required', 'in:full,employee'],
        'admin_permissions' => ['required_if:account_type,employee', 'array'],
        'admin_permissions.*' => ['string', 'in:' . implode(',', \App\Support\AdminSections::DELEGABLE)],
    ]);

    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'password' => Hash::make($request->password),
        'role' => UserRole::SUPER_ADMIN,
        'is_active' => true,
        'email_verified_at' => now(),
        'admin_permissions' => $request->account_type === 'employee'
            ? array_values($request->admin_permissions)
            : null,
    ]);

    return back()->with('success', 'Compte créé avec succès.');
}
```

> Le test `test_restricted_employee_cannot_create_users` passe grâce au middleware `admin.section` de Task 4 (route `utilisateurs.*` réservée aux admins complets) — aucune garde supplémentaire nécessaire ici.

- [ ] **Step 4: Lancer → succès** `php artisan test --filter=EmployeeCreationTest` — Expected: PASS.

- [ ] **Step 5: Commit** `git add -A && git commit -m "feat(rbac): création d'employés back-office avec sections"`

---

### Task 7: Redirection post-login des employés restreints

**Files:**
- Modify: `app/Models/User.php` (`dashboardRoute()`, ~l.311)
- Test: `tests/Unit/EmployeeDashboardRouteTest.php`

**Interfaces:**
- Consumes: `canAdminSection()`, `AdminSections::DELEGABLE` (Task 3).
- Produces: `User::dashboardRoute()` renvoie, pour un employé restreint, le nom de route de sa première section autorisée (ou `restaurant.subscription`-style repli sûr).

- [ ] **Step 1: Écrire le test qui échoue**

```php
<?php
namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeDashboardRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_admin_lands_on_dashboard(): void
    {
        $admin = User::factory()->superAdmin()->create(['admin_permissions' => null]);
        $this->assertSame('super-admin.dashboard', $admin->dashboardRoute());
    }

    public function test_restricted_employee_lands_on_first_section(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['orders', 'restaurants']]);
        // L'ordre canonique de DELEGABLE place 'restaurants' avant 'orders'
        $this->assertSame('super-admin.restaurants.index', $emp->dashboardRoute());
    }

    public function test_employee_with_no_section_lands_on_safe_fallback(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => []]);
        $this->assertSame('logout', $emp->dashboardRoute());
    }
}
```

- [ ] **Step 2: Lancer → échec** `php artisan test --filter=EmployeeDashboardRouteTest` — Expected: FAIL.

- [ ] **Step 3: Modifier `dashboardRoute()`.** Dans le `match` existant, pour `UserRole::SUPER_ADMIN`, remplacer le retour direct `'super-admin.dashboard'` par un appel à une méthode dédiée :

```php
UserRole::SUPER_ADMIN => $this->adminLandingRoute(),
```

et ajouter la méthode :

```php
private function adminLandingRoute(): string
{
    if ($this->admin_permissions === null) {
        return 'super-admin.dashboard';
    }

    $map = [
        'restaurants'   => 'super-admin.restaurants.index',
        'orders'        => 'super-admin.orders.index',
        'deliveries'    => 'super-admin.deliveries.index',
        'subscriptions' => 'super-admin.subscriptions.index',
        'crm'           => 'super-admin.commando.agents.index',
        'announcements' => 'super-admin.announcements.index',
        'customers'     => 'super-admin.customers.index',
        'finance'       => 'super-admin.finances.index',
    ];

    foreach (\App\Support\AdminSections::DELEGABLE as $key) {
        if (in_array($key, $this->admin_permissions, true)) {
            return $map[$key];
        }
    }

    return 'logout'; // employé sans aucune section : repli sûr
}
```

- [ ] **Step 4: Lancer → succès** `php artisan test --filter=EmployeeDashboardRouteTest` — Expected: PASS.

- [ ] **Step 5: Commit** `git add -A && git commit -m "feat(rbac): atterrissage post-login des employés sur leur première section"`

---

### Task 8: UI — sidebar filtrée + formulaire employé

**Files:**
- Modify: `resources/views/components/layouts/admin-super.blade.php` (liens de navigation)
- Modify: `resources/views/pages/super-admin/users.blade.php` (formulaire de création : champs + cases à cocher)
- Test: `tests/Feature/Admin/SidebarVisibilityTest.php`

**Interfaces:**
- Consumes: `canAdminSection()`, `isFullAdmin()`, `AdminSections::LABELS` (Task 3).

- [ ] **Step 1: Écrire le test qui échoue**

```php
<?php
namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_sidebar_hides_unauthorized_sections(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $html = $this->actingAs($emp)->get(route('super-admin.restaurants.index'))->getContent();

        $this->assertStringContainsString(route('super-admin.restaurants.index'), $html);
        $this->assertStringNotContainsString(route('super-admin.finances.index'), $html);
        $this->assertStringNotContainsString(route('super-admin.utilisateurs.index'), $html);
    }
}
```

- [ ] **Step 2: Lancer → échec** `php artisan test --filter=SidebarVisibilityTest` — Expected: FAIL.

- [ ] **Step 3: Filtrer la sidebar.** Dans `admin-super.blade.php`, entourer chaque `<a>` de navigation par la garde de section correspondante. Exemples (appliquer le motif à chaque lien) :

```blade
@if(auth()->user()->canAdminSection('restaurants'))
<a href="{{ route('super-admin.restaurants.index') }}" ...>...</a>
@endif

@if(auth()->user()->canAdminSection('orders'))
<a href="{{ route('super-admin.orders.index') }}" ...>...</a>
@endif
```

Pour les sections réservées (Utilisateurs, Config Paiement, Jeko, Paramètres, Statistiques, Activité) et le lien Dashboard, utiliser :

```blade
@if(auth()->user()->isFullAdmin())
<a href="{{ route('super-admin.utilisateurs.index') }}" ...>...</a>
@endif
```

Mapping des liens existants → garde :
`restaurants`→`canAdminSection('restaurants')` ; `orders`/`orders.live`→`orders` ; `deliveries`/`drivers`/`delivery-cities`/`delivery-zones`→`deliveries` ; `subscriptions`/`plans`→`subscriptions` ; `commando`→`crm` ; `announcements`/`promo-banners`/`push`→`announcements` ; `customers`→`customers` ; `transactions`/`finances`→`finance` ; `dashboard`/`utilisateurs`/`jeko`/`payment-settings`/`settings`/`stats`/`activity`→`isFullAdmin()`.

- [ ] **Step 4: Formulaire de création employé.** Dans `pages/super-admin/users.blade.php`, dans le formulaire POST vers `super-admin.utilisateurs.store`, ajouter le choix du type de compte et les cases à cocher (visible uniquement si `isFullAdmin`, ce que la route garantit déjà) :

```blade
<div x-data="{ type: 'employee' }">
  <label><input type="radio" name="account_type" value="full" x-model="type"> Admin complet</label>
  <label><input type="radio" name="account_type" value="employee" x-model="type" checked> Employé (accès limité)</label>

  <div x-show="type === 'employee'" class="mt-3 grid grid-cols-2 gap-2">
    @foreach(\App\Support\AdminSections::LABELS as $key => $label)
      <label class="flex items-center gap-2 text-sm">
        <input type="checkbox" name="admin_permissions[]" value="{{ $key }}">
        {{ $label }}
      </label>
    @endforeach
  </div>
</div>
```

- [ ] **Step 5: Lancer → succès** `php artisan test --filter=SidebarVisibilityTest` — Expected: PASS.

- [ ] **Step 6: Commit** `git add -A && git commit -m "feat(rbac): sidebar filtrée par section + formulaire employé"`

---

### Task 9: Vérification de non-régression

**Files:**
- Test: `tests/Feature/Admin/RbacRegressionTest.php`

- [ ] **Step 1: Écrire les tests**

```php
<?php
namespace Tests\Feature\Admin;

use App\Enums\RestaurantStatus;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_with_restaurants_can_approve(): void
    {
        $emp = User::factory()->superAdmin()->create(['admin_permissions' => ['restaurants']]);
        $restaurant = Restaurant::factory()->create(['status' => RestaurantStatus::PENDING]);

        $this->actingAs($emp)
            ->post(route('super-admin.restaurants.approve', $restaurant))
            ->assertRedirect();

        $this->assertSame(RestaurantStatus::ACTIVE, $restaurant->refresh()->status);
    }

    public function test_pending_restaurant_public_page_still_blocked(): void
    {
        $restaurant = Restaurant::factory()->create([
            'status' => RestaurantStatus::PENDING, 'slug' => 'chez-test',
        ]);

        $this->get(route('r.menu', $restaurant->slug))->assertStatus(503);
    }
}
```

- [ ] **Step 2: Lancer → succès** `php artisan test --filter=RbacRegressionTest` — Expected: PASS (si un test échoue, corriger la tâche d'origine, pas ce test).

- [ ] **Step 3: Suite complète** `php artisan test` — Expected: aucune régression.

- [ ] **Step 4: Commit** `git add -A && git commit -m "test(rbac): non-régression approbation employé + page publique PENDING"`

---

## Notes d'exécution
- Les states de factory `superAdmin()` / `restaurantAdmin()` sur `UserFactory` sont supposés (sinon les créer dans la première tâche qui les utilise — Task 2).
- Vérifier que `Restaurant::factory()` et `Subscription::factory()` existent ; sinon les créer au besoin (scaffolding de test).
- Après implémentation : `bash ~/deploy.sh` sur le VPS (migration incluse).

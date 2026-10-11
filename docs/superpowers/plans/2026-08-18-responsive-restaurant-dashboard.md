# Responsive Restaurant Dashboard — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal :** Rendre les 27 pages du dashboard restaurant pleinement responsives sur mobile (375px+) et tablette (768px+), sans régression desktop.

**Architecture :** Chaque tâche applique les patterns définis dans la spec (card stack, modals bottom-sheet, formulaires 1-colonne mobile, sticky save bar). Aucun JS externe ajouté. Tailwind CSS v4 + Alpine.js existants.

**Tech Stack :** Laravel 11, Blade, Tailwind CSS v4, Alpine.js

**Spec :** `docs/superpowers/specs/2026-08-18-responsive-restaurant-dashboard.md`

## Global Constraints

- Breakpoints : mobile `< 768px`, tablette `768–1023px`, desktop `≥ 1024px`
- Touch targets ≥ 44×44px sur tous les éléments cliquables
- Pattern card stack : `<div class="block lg:hidden ...">` mobile + `<div class="hidden lg:block ...">` desktop
- Bottom nav height = 64px → ajouter `pb-20 lg:pb-0` sur le `<main>` du layout
- Sticky save bar mobile : `fixed bottom-16 left-0 right-0 z-30 bg-white/95 backdrop-blur border-t border-neutral-200 px-4 py-3`
- Modals : `items-end sm:items-center` + inner `rounded-t-2xl sm:rounded-2xl max-h-[90vh] overflow-y-auto`
- Aucun scroll horizontal non-intentionnel à 375px
- Ne pas modifier les contrôleurs, routes, ou modèles — uniquement les vues Blade
- Commit après chaque tâche avec message conventionnel `fix(responsive): ...`

---

## Task 1 — Layout global (`admin-restaurant.blade.php`)

**Files :**
- Modify : `resources/views/components/layouts/admin-restaurant.blade.php`

**Interfaces :**
- Produit : `pb-20 lg:pb-0` sur `<main>` pour dégager la bottom nav sur toutes les pages

- [ ] **Step 1 : Ajouter `pb-20 lg:pb-0` sur le `<main>` du layout**

Trouver la balise `<main` dans le fichier et ajouter `pb-20 lg:pb-0` à ses classes existantes. Elle ressemble à :
```html
<main class="flex-1 overflow-y-auto ...">
```
Doit devenir :
```html
<main class="flex-1 overflow-y-auto pb-20 lg:pb-0 ...">
```

- [ ] **Step 2 : Topbar mobile — titre tronqué et padding réduit**

Trouver le bloc `<h1` du titre de page dans la topbar (autour de la ligne 572) :
```html
<h1 class="text-base sm:text-xl font-bold text-neutral-900 truncate">{{ $title ?? 'Dashboard' }}</h1>
```
Vérifier que `truncate` est bien présent. Si non, l'ajouter.

Trouver le container de la topbar (`h-16` ou `px-4`) et s'assurer qu'il a `px-4 sm:px-6`.

- [ ] **Step 3 : Vérifier que l'overlay mobile sidebar ferme au tap**

Chercher `@click="mobileOpen = false"` sur l'overlay div. Si absent, l'ajouter.

- [ ] **Step 4 : Commit**

```bash
git add resources/views/components/layouts/admin-restaurant.blade.php
git commit -m "fix(responsive): layout main pb-20 mobile + topbar truncate"
```

---

## Task 2 — Commandes liste (`orders.blade.php`)

**Files :**
- Modify : `resources/views/pages/restaurant/orders.blade.php`

- [ ] **Step 1 : Identifier la structure actuelle**

Lire le fichier. Repérer : le conteneur principal des commandes (table ou div liste), les filtres/barres de recherche, la pagination.

- [ ] **Step 2 : Rendre les filtres responsives**

Wraper la barre filtres+recherche dans :
```html
<div class="flex flex-col sm:flex-row gap-2 flex-wrap">
  <!-- champs existants avec class="w-full sm:w-auto" sur boutons -->
</div>
```

- [ ] **Step 3 : Ajouter la vue card stack mobile**

Juste avant le tableau existant (ou après selon la structure), ajouter :
```html
{{-- Vue mobile (card stack) --}}
<div class="block lg:hidden space-y-3 px-4 py-3">
  @forelse($orders as $order)
  <div class="bg-white rounded-2xl border border-neutral-200 p-4 shadow-sm">
    <div class="flex items-center justify-between mb-2">
      <span class="font-semibold text-sm text-neutral-900">#{{ $order->order_number ?? $order->id }}</span>
      <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
        {{ $order->status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
           ($order->status === 'confirmed' ? 'bg-blue-100 text-blue-800' :
           ($order->status === 'delivered' ? 'bg-green-100 text-green-800' :
           'bg-neutral-100 text-neutral-700')) }}">
        {{ ucfirst($order->status) }}
      </span>
    </div>
    <p class="text-sm text-neutral-600 truncate mb-1">{{ $order->customer_name ?? $order->customer?->name ?? '—' }}</p>
    <p class="text-xs text-neutral-400 mb-3">{{ $order->created_at->format('d/m H:i') }}</p>
    <div class="flex items-center justify-between">
      <span class="text-base font-bold text-neutral-900">{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
      <a href="{{ route('restaurant.orders.show', $order) }}"
         class="px-4 py-2 rounded-xl text-sm font-semibold text-white"
         style="background:#D45E0C">
        Voir
      </a>
    </div>
  </div>
  @empty
  <p class="text-center text-neutral-400 py-8">Aucune commande</p>
  @endforelse
</div>
```

Wrapper le tableau existant :
```html
{{-- Vue desktop (tableau) --}}
<div class="hidden lg:block overflow-x-auto">
  <!-- tableau existant -->
</div>
```

- [ ] **Step 4 : Pagination responsive**

Chercher le composant pagination. S'assurer qu'il est dans un `<div class="px-4 py-3">`. Si la pagination est du texte + liens, laisser tel quel — Tailwind la gérera.

- [ ] **Step 5 : Commit**

```bash
git add resources/views/pages/restaurant/orders.blade.php
git commit -m "fix(responsive): orders list — card stack mobile + filtres flex-col"
```

---

## Task 3 — Détail commande (`order-show.blade.php`)

**Files :**
- Modify : `resources/views/pages/restaurant/order-show.blade.php`

- [ ] **Step 1 : Lire et repérer le layout**

Le fichier a une grille `grid-cols-3` ou similaire. Identifier le conteneur principal.

- [ ] **Step 2 : Layout 1 colonne mobile**

```html
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
  <!-- colonne principale (infos commande + articles) -->
  <div class="lg:col-span-2 space-y-4">...</div>
  <!-- colonne latérale (infos client, actions) -->
  <div class="space-y-4">...</div>
</div>
```

- [ ] **Step 3 : Articles de la commande → card stack mobile**

Si les articles sont dans un `<table>`, appliquer le pattern card stack (même structure que Task 2 Step 3 mais pour les articles).

- [ ] **Step 4 : Boutons d'action responsive**

```html
<div class="flex flex-col sm:flex-row gap-2">
  <!-- boutons existants avec class="w-full sm:w-auto" -->
</div>
```

- [ ] **Step 5 : Sticky action bar mobile si boutons importants**

Si la page a des boutons Accepter/Refuser critiques, ajouter la sticky bar :
```html
<div class="fixed bottom-16 left-0 right-0 z-30 bg-white/95 backdrop-blur border-t border-neutral-200 px-4 py-3 flex gap-3 lg:hidden">
  <!-- boutons d'action principaux -->
</div>
<div class="h-20 lg:hidden"></div>
```

- [ ] **Step 6 : Commit**

```bash
git add resources/views/pages/restaurant/order-show.blade.php
git commit -m "fix(responsive): order-show — layout 1col mobile + sticky actions"
```

---

## Task 4 — Kanban & Rush (`orders-kanban.blade.php`, `orders-rush.blade.php`)

**Files :**
- Modify : `resources/views/pages/restaurant/orders-kanban.blade.php`
- Modify : `resources/views/pages/restaurant/orders-rush.blade.php`

- [ ] **Step 1 : Kanban — scroll horizontal snap**

Trouver le conteneur des colonnes kanban (probablement `flex gap-4` ou `grid`). Le wrapper dans :
```html
<div class="flex gap-4 overflow-x-auto snap-x snap-mandatory pb-4 -mx-4 px-4 lg:mx-0 lg:px-0 lg:overflow-visible">
  <!-- chaque colonne kanban -->
  <div class="snap-start flex-shrink-0 w-[82vw] sm:w-72 lg:w-auto lg:flex-1 ...">
    ...
  </div>
</div>
```

- [ ] **Step 2 : Rush — grille responsive**

Trouver la grille des tickets rush. Remplacer la valeur fixe par :
```html
class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
```

- [ ] **Step 3 : Rush — boutons pleine largeur mobile**

Dans `orders-rush-card.blade.php` (si modifiable), s'assurer que les boutons d'action sont `w-full`.

- [ ] **Step 4 : Commit**

```bash
git add resources/views/pages/restaurant/orders-kanban.blade.php resources/views/pages/restaurant/orders-rush.blade.php
git commit -m "fix(responsive): kanban scroll-snap + rush grid 1→4 cols"
```

---

## Task 5 — Plats & Catégories (`dishes.blade.php`, `dishes-create.blade.php`, `categories.blade.php`)

**Files :**
- Modify : `resources/views/pages/restaurant/dishes.blade.php`
- Modify : `resources/views/pages/restaurant/dishes-create.blade.php`
- Modify : `resources/views/pages/restaurant/categories.blade.php`

- [ ] **Step 1 : `dishes.blade.php` — card stack mobile**

Card par plat sur mobile :
```html
{{-- Mobile --}}
<div class="block lg:hidden space-y-3 px-4 py-3">
  @foreach($dishes as $dish)
  <div class="bg-white rounded-2xl border border-neutral-200 p-3 flex items-center gap-3">
    @if($dish->image)
    <img src="{{ Storage::url($dish->image) }}" class="w-14 h-14 rounded-xl object-cover flex-shrink-0">
    @else
    <div class="w-14 h-14 rounded-xl bg-neutral-100 flex items-center justify-center flex-shrink-0">
      <svg class="w-6 h-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    </div>
    @endif
    <div class="flex-1 min-w-0">
      <p class="font-semibold text-sm text-neutral-900 truncate">{{ $dish->name }}</p>
      <p class="text-xs text-neutral-500">{{ $dish->category?->name ?? '—' }} · {{ number_format($dish->price, 0, ',', ' ') }} FCFA</p>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
      {{-- toggle disponibilité --}}
      {{-- bouton edit --}}
    </div>
  </div>
  @endforeach
</div>
{{-- Desktop tableau existant --}}
<div class="hidden lg:block">...</div>
```

- [ ] **Step 2 : `dishes-create.blade.php` — formulaire 1 colonne mobile**

Trouver les grilles dans le formulaire. Remplacer `grid-cols-2` par `grid-cols-1 md:grid-cols-2`.
Ajouter la sticky save bar mobile :
```html
<div class="fixed bottom-16 left-0 right-0 z-30 bg-white/95 backdrop-blur border-t border-neutral-200 px-4 py-3 flex gap-3 lg:hidden">
  <a href="{{ route('restaurant.plats.index') }}" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl border-2 border-neutral-200 text-sm font-semibold text-neutral-700">Annuler</a>
  <button type="submit" form="dish-form" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white" style="background:#D45E0C">Enregistrer</button>
</div>
<div class="h-20 lg:hidden"></div>
```
(Ajouter `id="dish-form"` sur le `<form>` si absent.)

- [ ] **Step 3 : `categories.blade.php` — grille et modal**

Grille catégories : `grid-cols-2 sm:grid-cols-3 lg:grid-cols-4`.
Modal : ajouter `items-end sm:items-center` sur le conteneur overlay et `rounded-t-2xl sm:rounded-2xl` sur le panneau.

- [ ] **Step 4 : Commit**

```bash
git add resources/views/pages/restaurant/dishes.blade.php resources/views/pages/restaurant/dishes-create.blade.php resources/views/pages/restaurant/categories.blade.php
git commit -m "fix(responsive): plats card-stack + form 1col + catégories grid"
```

---

## Task 6 — Dashboard & QR Code & Chambres

**Files :**
- Modify : `resources/views/pages/restaurant/dashboard.blade.php`
- Modify : `resources/views/pages/restaurant/qrcode.blade.php`
- Modify : `resources/views/pages/restaurant/hotel-rooms.blade.php`

- [ ] **Step 1 : `dashboard.blade.php` — grilles KPI**

Trouver la grille de stats (probablement `grid-cols-4` ou similaire). Remplacer par `grid-cols-2 lg:grid-cols-4`.
Les graphiques : ajouter `w-full overflow-hidden` sur leurs conteneurs.
Section commandes récentes : si tableau → card stack mobile.

- [ ] **Step 2 : `qrcode.blade.php` — layout 1 colonne mobile**

Layout : `grid-cols-1 lg:grid-cols-2`.
QR image : `<div class="flex justify-center"><img class="w-48 h-48 lg:w-64 lg:h-64"></div>`.
Boutons download : `flex-col sm:flex-row gap-2`.

- [ ] **Step 3 : `hotel-rooms.blade.php` — grille chambres**

Grille : `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`.
Card chambre : boutons `w-full` sur mobile.

- [ ] **Step 4 : Commit**

```bash
git add resources/views/pages/restaurant/dashboard.blade.php resources/views/pages/restaurant/qrcode.blade.php resources/views/pages/restaurant/hotel-rooms.blade.php
git commit -m "fix(responsive): dashboard grid 2cols + qrcode 1col + chambres grid"
```

---

## Task 7 — Paramètres (`settings.blade.php`)

**Files :**
- Modify : `resources/views/pages/restaurant/settings.blade.php`

C'est la page la plus dense — plusieurs sections de formulaires.

- [ ] **Step 1 : Lire la structure complète du fichier**

Identifier : les sections/onglets, les grilles, les zones d'upload, les boutons.

- [ ] **Step 2 : Tous les formulaires en 1 colonne mobile**

Remplacer toutes les occurrences de `grid-cols-2` (sans préfixe responsive) par `grid-cols-1 md:grid-cols-2`.
Remplacer `grid-cols-3` par `grid-cols-1 md:grid-cols-2 lg:grid-cols-3`.

- [ ] **Step 3 : Zones d'upload**

Pour chaque zone d'upload (logo, bannière, photo) : `w-full`.

- [ ] **Step 4 : Onglets/tabs responsive**

Si des onglets existent :
```html
<div class="flex overflow-x-auto gap-1 pb-2 -mx-4 px-4 sm:mx-0 sm:px-0 sm:flex-wrap">
  <!-- onglets existants -->
</div>
```

- [ ] **Step 5 : Sticky save bar mobile**

À la fin du formulaire principal, avant la fermeture du `<form>` :
```html
<div class="fixed bottom-16 left-0 right-0 z-30 bg-white/95 backdrop-blur border-t border-neutral-200 px-4 py-3 flex gap-3 lg:hidden">
  <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-xl text-sm font-semibold text-white" style="background:#D45E0C">
    Enregistrer les modifications
  </button>
</div>
<div class="h-20 lg:hidden"></div>
```

- [ ] **Step 6 : Commit**

```bash
git add resources/views/pages/restaurant/settings.blade.php
git commit -m "fix(responsive): settings — form 1col mobile + sticky save + tabs scroll"
```

---

## Task 8 — Clients & Réservations

**Files :**
- Modify : `resources/views/pages/restaurant/customers.blade.php`
- Modify : `resources/views/pages/restaurant/customer-show.blade.php`
- Modify : `resources/views/pages/restaurant/reservations.blade.php`
- Modify : `resources/views/pages/restaurant/reservation-show.blade.php`

- [ ] **Step 1 : `customers.blade.php` — card stack + filtres**

Filtres : `flex-col sm:flex-row gap-2`.
Card client mobile :
```html
<div class="bg-white rounded-2xl border border-neutral-200 p-4">
  <div class="flex items-center gap-3 mb-2">
    <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0">
      <span class="text-sm font-bold text-primary-600">{{ substr($customer->name, 0, 1) }}</span>
    </div>
    <div class="flex-1 min-w-0">
      <p class="font-semibold text-sm truncate">{{ $customer->name }}</p>
      <p class="text-xs text-neutral-500 truncate">{{ $customer->email ?? $customer->phone ?? '—' }}</p>
    </div>
  </div>
  <div class="flex items-center justify-between">
    <span class="text-xs text-neutral-400">{{ $customer->orders_count ?? 0 }} commande(s)</span>
    <a href="{{ route('restaurant.customers.show', $customer) }}" class="text-xs font-semibold text-primary-600">Voir →</a>
  </div>
</div>
```

- [ ] **Step 2 : `customer-show.blade.php` — stats 2 cols mobile**

Stats : `grid-cols-2 sm:grid-cols-4`.
Historique commandes : card stack (même pattern que Task 2).

- [ ] **Step 3 : `reservations.blade.php` — card stack**

Card réservation mobile :
```html
<div class="bg-white rounded-2xl border border-neutral-200 p-4">
  <div class="flex items-center justify-between mb-1">
    <span class="font-semibold text-sm">{{ $reservation->customer_name }}</span>
    <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-700">{{ $reservation->status }}</span>
  </div>
  <p class="text-xs text-neutral-500">{{ $reservation->date?->format('d/m/Y H:i') }} · Table {{ $reservation->table_number ?? '—' }}</p>
  <p class="text-xs text-neutral-500">{{ $reservation->guests }} pers.</p>
</div>
```

- [ ] **Step 4 : `reservation-show.blade.php` — 1 colonne mobile**

Layout : `grid-cols-1 lg:grid-cols-2`.
Formulaire statut : `w-full`.

- [ ] **Step 5 : Commit**

```bash
git add resources/views/pages/restaurant/customers.blade.php resources/views/pages/restaurant/customer-show.blade.php resources/views/pages/restaurant/reservations.blade.php resources/views/pages/restaurant/reservation-show.blade.php
git commit -m "fix(responsive): clients + réservations — card stack mobile"
```

---

## Task 9 — Stock (`ingredients.blade.php`, suppliers, reports)

**Files :**
- Modify : `resources/views/pages/restaurant/ingredients.blade.php`
- Modify : `resources/views/pages/restaurant/ingredient-edit.blade.php`
- Modify : `resources/views/pages/restaurant/ingredient-show.blade.php`
- Modify : `resources/views/pages/restaurant/ingredient-categories.blade.php`
- Modify : `resources/views/pages/restaurant/suppliers.blade.php`
- Modify : `resources/views/pages/restaurant/supplier-show.blade.php`
- Modify : `resources/views/pages/restaurant/stock-report.blade.php`

- [ ] **Step 1 : `ingredients.blade.php` — card stack avec colonnes essentielles**

Colonnes mobile : Nom + Quantité + Alerte. Masquer Coût/Fournisseur/Date avec `hidden lg:table-cell`.
Card ingrédient mobile :
```html
<div class="bg-white rounded-2xl border border-neutral-200 p-4 flex items-center gap-3">
  <div class="flex-1 min-w-0">
    <p class="font-semibold text-sm truncate">{{ $ingredient->name }}</p>
    <p class="text-xs text-neutral-500">{{ $ingredient->quantity }} {{ $ingredient->unit }}</p>
  </div>
  @if($ingredient->is_low_stock)
  <span class="text-xs px-2 py-1 rounded-full bg-red-100 text-red-700">Alerte</span>
  @endif
  <div class="flex gap-2">
    {{-- boutons entrée/sortie --}}
  </div>
</div>
```

- [ ] **Step 2 : Formulaires ingrédients (`ingredient-edit.blade.php`, `ingredient-show.blade.php`, `ingredient-categories.blade.php`)**

Tous les `grid-cols-2` → `grid-cols-1 md:grid-cols-2`.
Modals : bottom-sheet pattern (ajouter `items-end sm:items-center` + `rounded-t-2xl sm:rounded-2xl`).

- [ ] **Step 3 : `suppliers.blade.php` et `supplier-show.blade.php`**

Card fournisseur mobile (même pattern ingrédient).
`supplier-show.blade.php` : `grid-cols-1 lg:grid-cols-3`.

- [ ] **Step 4 : `stock-report.blade.php` — scroll horizontal forcé**

Wrapper le tableau dans :
```html
<div class="overflow-x-auto -mx-4 px-4 sm:mx-0 sm:px-0">
  <div class="min-w-[600px]">
    <!-- tableau existant -->
  </div>
</div>
```

- [ ] **Step 5 : Commit**

```bash
git add resources/views/pages/restaurant/ingredients.blade.php resources/views/pages/restaurant/ingredient-edit.blade.php resources/views/pages/restaurant/ingredient-show.blade.php resources/views/pages/restaurant/ingredient-categories.blade.php resources/views/pages/restaurant/suppliers.blade.php resources/views/pages/restaurant/supplier-show.blade.php resources/views/pages/restaurant/stock-report.blade.php
git commit -m "fix(responsive): stock — card stack + formulaires 1col + rapport scroll"
```

---

## Task 10 — Abonnement & Analytics

**Files :**
- Modify : `resources/views/pages/restaurant/subscription.blade.php`
- Modify : `resources/views/pages/restaurant/subscription-plans.blade.php`
- Modify : `resources/views/pages/restaurant/subscription-invoices.blade.php`
- Modify : `resources/views/pages/restaurant/analytics.blade.php`

- [ ] **Step 1 : `subscription-plans.blade.php` — grille plans**

Plans : `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`. Cards pleine largeur mobile.

- [ ] **Step 2 : `subscription.blade.php` — info abonnement**

Layout : `grid-cols-1 lg:grid-cols-2`. Boutons : `flex-col sm:flex-row gap-2`.

- [ ] **Step 3 : `subscription-invoices.blade.php` — card stack**

Card facture mobile :
```html
<div class="bg-white rounded-2xl border border-neutral-200 p-4">
  <div class="flex items-center justify-between mb-2">
    <span class="font-semibold text-sm">{{ $invoice->reference ?? '#'.$invoice->id }}</span>
    <span class="text-xs px-2 py-1 rounded-full {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $invoice->status }}</span>
  </div>
  <p class="text-xs text-neutral-500">{{ $invoice->created_at->format('d/m/Y') }} · {{ number_format($invoice->amount, 0, ',', ' ') }} FCFA</p>
  @if($invoice->pdf_url ?? null)
  <a href="{{ $invoice->pdf_url }}" class="mt-2 inline-flex items-center text-xs text-primary-600 font-semibold">Télécharger PDF →</a>
  @endif
</div>
```

- [ ] **Step 4 : `analytics.blade.php` — métriques et graphiques**

Métriques : `grid-cols-2 lg:grid-cols-4`.
Graphiques : `w-full min-h-[200px] overflow-hidden`.
Sélecteur période : `w-full sm:w-auto`.
Tableau top plats : card stack mobile.

- [ ] **Step 5 : Commit**

```bash
git add resources/views/pages/restaurant/subscription.blade.php resources/views/pages/restaurant/subscription-plans.blade.php resources/views/pages/restaurant/subscription-invoices.blade.php resources/views/pages/restaurant/analytics.blade.php
git commit -m "fix(responsive): abonnement + analytics — grilles responsives mobile"
```

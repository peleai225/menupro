# Spec — Responsivité complète du dashboard restaurant

**Objectif :** Rendre chaque page du dashboard restaurant (`/dashboard/*`) pleinement utilisable sur mobile (320px–767px) et tablette (768px–1023px), sans régression desktop.

**Date :** 2026-08-18
**Périmètre :** `resources/views/pages/restaurant/` + `resources/views/components/layouts/admin-restaurant.blade.php`
**Stack :** Laravel 11, Blade, Tailwind CSS v4, Alpine.js

---

## 1. Principes directeurs

| Principe | Règle |
|----------|-------|
| Mobile-first | Écrire d'abord pour 375px, élargir avec `md:` / `lg:` |
| Touch targets | Toute zone cliquable ≥ 44×44px |
| Tableaux | Sur mobile : transformer en **card stack** (une ligne = une carte verticale) |
| Formulaires | Une colonne sur mobile, grille sur desktop |
| Modals | `w-full` + `max-h-[90vh] overflow-y-auto` sur mobile, centré sur desktop |
| Padding content | `px-4` mobile → `px-6 md:px-8` desktop |
| Espacements section | `py-4` mobile → `py-6 md:py-8` desktop |
| Texte tronqué | `truncate` sur tous les champs identifiants dans les listes |
| Actions de ligne | Toujours visibles sur mobile (pas de hover-only) |

---

## 2. Points de rupture

```
mobile   : < 768px   (xs, sm)
tablet   : 768–1023px (md)
desktop  : ≥ 1024px   (lg+)
```

Le layout sidebar est masqué sous `lg:` — la bottom nav prend le relais (déjà fait).

---

## 3. Layout global (`admin-restaurant.blade.php`)

- [x] Bottom nav mobile refaite (commit `138743c`)
- [ ] **Topbar mobile** : réduire le padding `px-4`, masquer les éléments non-essentiels sous `sm:hidden`, s'assurer que le titre de page est `truncate`
- [ ] **Main content** : ajouter `pb-20 lg:pb-0` pour que le contenu ne passe pas sous la bottom nav
- [ ] **Sidebar drawer** : vérifier que l'overlay ferme bien au tap et que le scroll est fluide sur iOS

---

## 4. Page par page

### 4.1 Dashboard (`dashboard.blade.php`)

**Éléments à corriger :**
- Grille de stats KPI : `grid-cols-1 sm:grid-cols-2 lg:grid-cols-4`
- Graphiques (charts) : `w-full` + `min-h-[200px]` sur mobile, désactiver les légendes latérales
- Section "Commandes récentes" : transformer en card stack sur mobile
- Boutons d'action rapide : pleine largeur sur mobile (`w-full sm:w-auto`)

---

### 4.2 Commandes — Liste (`orders.blade.php`)

**Problème principal :** liste tabulaire non adaptée mobile.

**Pattern à appliquer (card stack) :**
```html
<!-- Mobile : une commande = une carte -->
<div class="block lg:hidden space-y-3 px-4">
  @foreach($orders as $order)
  <div class="bg-white rounded-2xl border border-neutral-200 p-4 shadow-sm">
    <div class="flex items-center justify-between mb-2">
      <span class="font-semibold text-sm">#{{ $order->number }}</span>
      <x-order-status-badge :status="$order->status"/>
    </div>
    <p class="text-sm text-neutral-600 truncate">{{ $order->customer_name }}</p>
    <div class="flex items-center justify-between mt-3">
      <span class="text-base font-bold text-neutral-900">{{ $order->total }} FCFA</span>
      <a href="{{ route('restaurant.orders.show', $order) }}" class="btn btn-sm btn-primary">Voir</a>
    </div>
  </div>
  @endforeach
</div>
<!-- Desktop : tableau existant -->
<div class="hidden lg:block">
  <!-- tableau existant -->
</div>
```

**Filtres :** replier dans un drawer/accordion `x-data` sur mobile.

---

### 4.3 Kanban commandes (`orders-kanban.blade.php`)

**Problème :** colonnes kanban horizontales non scrollables sur mobile.

**Fix :**
```html
<div class="flex gap-4 overflow-x-auto snap-x snap-mandatory pb-4 px-4 lg:px-0 lg:overflow-visible">
  <!-- chaque colonne -->
  <div class="snap-start flex-shrink-0 w-[80vw] sm:w-72 lg:w-auto lg:flex-1">
    ...
  </div>
</div>
```
Ajouter `-webkit-overflow-scrolling: touch` via style inline si nécessaire.

---

### 4.4 Rush (`orders-rush.blade.php`)

- Grille des tickets : `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`
- Boutons d'action des tickets : `w-full` sur mobile
- Timer : taille fixe, ne pas laisser déborder

---

### 4.5 Détail commande (`order-show.blade.php`)

- Layout 2 colonnes → `grid-cols-1 lg:grid-cols-3`
- Colonne infos client : passer en pleine largeur mobile
- Tableau des articles : card stack mobile
- Boutons d'action (Accepter, Refuser, Imprimer) : `flex flex-col sm:flex-row gap-2 w-full sm:w-auto`

---

### 4.6 Plats (`dishes.blade.php`)

**Tableau → card stack mobile :**
```html
<!-- Mobile card -->
<div class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-neutral-200">
  <img class="w-14 h-14 rounded-xl object-cover flex-shrink-0">
  <div class="flex-1 min-w-0">
    <p class="font-semibold text-sm truncate">Nom du plat</p>
    <p class="text-xs text-neutral-500">Catégorie · Prix</p>
  </div>
  <div class="flex items-center gap-2 flex-shrink-0">
    <!-- toggle disponibilité + bouton edit -->
  </div>
</div>
```

---

### 4.7 Créer/éditer un plat (`dishes-create.blade.php`)

- Formulaire 2 colonnes → `grid-cols-1 lg:grid-cols-2`
- Zone d'upload image : pleine largeur, hauteur fixe `h-48`
- Sélecteur catégories/extras : `w-full`
- Barre d'actions sticky en bas sur mobile :
```html
<div class="sticky bottom-20 lg:bottom-0 bg-white border-t border-neutral-200 p-4 flex gap-3 lg:hidden">
  <button class="flex-1 btn btn-outline">Annuler</button>
  <button class="flex-1 btn btn-primary">Enregistrer</button>
</div>
```

---

### 4.8 Catégories (`categories.blade.php`)

- Grille de catégories : `grid-cols-2 sm:grid-cols-3 lg:grid-cols-4`
- Cards catégorie : padding réduit mobile `p-3`, icône `w-8 h-8`
- Modal ajout/édition : `w-full mx-4 rounded-2xl` sur mobile

---

### 4.9 Clients (`customers.blade.php`)

- Tableau → card stack mobile (même pattern que commandes)
- Barre de recherche + filtres : `flex-col gap-2` mobile, `flex-row` desktop
- Export CSV : bouton pleine largeur mobile

---

### 4.10 Fiche client (`customer-show.blade.php`)

- Stats en haut : `grid-cols-2 sm:grid-cols-4`
- Historique commandes : card stack mobile

---

### 4.11 Réservations (`reservations.blade.php`)

- Liste : card stack mobile avec heure + nom + table + statut
- Formulaire nouvelle réservation : `grid-cols-1 md:grid-cols-2`
- Calendrier (si présent) : `w-full overflow-x-auto`

---

### 4.12 Stock — Ingrédients (`ingredients.blade.php`)

- Tableau : card stack mobile
- Colonnes à afficher mobile : Nom + Quantité + Unité + Alerte
- Colonnes à masquer mobile (`hidden lg:table-cell`) : Coût, Fournisseur, Dernière MAJ
- Boutons d'action (entrée/sortie) : icônes seules sur mobile avec `title` accessible

---

### 4.13 Créer/éditer ingrédient (`ingredient-edit.blade.php`, `ingredient-show.blade.php`)

- Formulaire : `grid-cols-1 md:grid-cols-2`
- Historique mouvements : card stack

---

### 4.14 Fournisseurs (`suppliers.blade.php`, `supplier-show.blade.php`)

- Liste : card stack mobile
- Fiche fournisseur : `grid-cols-1 lg:grid-cols-3`
- Tableau ingrédients liés : card stack

---

### 4.15 Rapport stock (`stock-report.blade.php`)

- Tableau rapport : scroll horizontal forcé `overflow-x-auto` dans un wrapper dédié
- En-tête du rapport : `flex-col gap-2` mobile

---

### 4.16 QR Code (`qrcode.blade.php`)

- Layout : `grid-cols-1 lg:grid-cols-2`
- QR affiché : centré, `max-w-[200px] mx-auto`
- Boutons download : `flex-col w-full gap-2` mobile

---

### 4.17 Chambres hôtel (`hotel-rooms.blade.php`)

- Grille chambres : `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`
- Card chambre : QR intégré, bouton pleine largeur mobile

---

### 4.18 Paramètres (`settings.blade.php`)

**C'est souvent la page la plus dense.**
- Sections de paramètres : `space-y-6`, chaque section dans une card
- Formulaires imbriqués : tous en `grid-cols-1 md:grid-cols-2`
- Upload logo/bannière : pleine largeur, preview centrée
- Bouton "Enregistrer" : sticky bottom mobile (même pattern que dishes-create)
- Onglets de paramètres (si présents) : scroll horizontal `overflow-x-auto` sur mobile

---

### 4.19 Abonnement (`subscription.blade.php`, `subscription-plans.blade.php`)

- Plans tarifaires : `grid-cols-1 sm:grid-cols-2 lg:grid-cols-3`
- Card plan : pleine largeur mobile
- Tableau factures (`subscription-invoices.blade.php`) : card stack mobile

---

### 4.20 Analytics (`analytics.blade.php`)

- Grille métriques : `grid-cols-2 lg:grid-cols-4`
- Graphiques : `w-full min-h-[180px]`
- Sélecteur période : `w-full` mobile
- Tableau top plats : card stack mobile

---

## 5. Composants transversaux

### 5.1 Pattern table → card stack (à extraire en composant)

Chaque tableau doit être doublé d'une version card pour mobile. Convention :

```html
<!-- Vue table (desktop) -->
<div class="hidden lg:block overflow-x-auto">
  <table>...</table>
</div>

<!-- Vue card (mobile) -->
<div class="lg:hidden space-y-3 p-4">
  @foreach(...)
    <div class="bg-white rounded-2xl border p-4">...</div>
  @endforeach
</div>
```

### 5.2 Modals

Toutes les modals doivent avoir :
```html
class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4"
<!-- inner -->
class="w-full sm:max-w-lg rounded-t-2xl sm:rounded-2xl max-h-[90vh] overflow-y-auto"
```
Sur mobile : bottom sheet (glisse depuis le bas). Sur desktop : centré.

### 5.3 Formulaires

Pattern uniforme :
```html
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
  <div class="md:col-span-2"><!-- champ pleine largeur --></div>
  <div><!-- champ demi --></div>
  <div><!-- champ demi --></div>
</div>
```

### 5.4 Pagination

Sur mobile, réduire à `Préc · [page] · Suiv` uniquement :
```html
<div class="flex items-center justify-between">
  <a>← Préc</a>
  <span>Page X / Y</span>
  <a>Suiv →</a>
</div>
```

### 5.5 Filtres / Barres de recherche

```html
<div class="flex flex-col sm:flex-row gap-2">
  <input class="flex-1" placeholder="Rechercher...">
  <button class="sm:w-auto w-full">Filtrer</button>
  <button class="sm:w-auto w-full">Export</button>
</div>
```

### 5.6 Sticky action bar mobile (formulaires longs)

À ajouter sur toutes les pages avec formulaire long :
```html
<div class="fixed bottom-16 left-0 right-0 z-30 bg-white/95 backdrop-blur border-t border-neutral-200 px-4 py-3 flex gap-3 lg:hidden">
  <button class="flex-1 btn btn-outline">Annuler</button>
  <button type="submit" class="flex-1 btn btn-primary">Enregistrer</button>
</div>
<!-- Spacer pour que le contenu ne passe pas dessous -->
<div class="h-20 lg:hidden"></div>
```

---

## 6. Ordre de priorité

| Priorité | Pages | Raison |
|----------|-------|--------|
| 🔴 P1 — Critique | `orders.blade.php`, `order-show.blade.php`, `orders-kanban.blade.php`, `orders-rush.blade.php` | Utilisées en temps réel, en salle, sur mobile |
| 🔴 P1 — Critique | `dishes.blade.php`, `dishes-create.blade.php` | Gestion menu quotidienne |
| 🟠 P2 — Important | `dashboard.blade.php`, `settings.blade.php`, `categories.blade.php` | Accès fréquent |
| 🟠 P2 — Important | `qrcode.blade.php`, `hotel-rooms.blade.php` | Fonctionnalités terrain |
| 🟡 P3 — Normal | `customers.blade.php`, `reservations.blade.php`, `analytics.blade.php` | Consultation régulière |
| 🟢 P4 — Secondaire | Pages stock (ingrédients, fournisseurs, rapports) | Usage back-office |
| 🟢 P4 — Secondaire | `subscription*.blade.php` | Usage ponctuel |

---

## 7. Checklist de test par page

Pour chaque page, valider :

- [ ] Aucun scroll horizontal non-intentionnel à 375px
- [ ] Tous les boutons/liens ≥ 44px de hauteur
- [ ] Textes lisibles sans zoom (≥ 14px)
- [ ] Formulaires utilisables au clavier virtuel (pas masqués derrière le keyboard)
- [ ] Tableaux transformés en cards ou scrollables horizontalement
- [ ] Modals accessibles et fermables facilement (tap outside, bouton ×)
- [ ] Bottom nav pas cachée par le contenu (padding-bottom correct)
- [ ] Badges et statuts visibles sur fond mobile

---

## 8. Fichiers à modifier

| Fichier | Action principale |
|---------|-----------------|
| `admin-restaurant.blade.php` | Topbar mobile, main `pb-20`, sidebar drawer |
| `pages/restaurant/dashboard.blade.php` | Grilles stats, card stack commandes récentes |
| `pages/restaurant/orders.blade.php` | Card stack mobile, filtres accordion |
| `pages/restaurant/order-show.blade.php` | Layout 1col mobile, sticky actions |
| `pages/restaurant/orders-kanban.blade.php` | Scroll horizontal snap |
| `pages/restaurant/orders-rush.blade.php` | Grille tickets responsive |
| `pages/restaurant/dishes.blade.php` | Card stack mobile |
| `pages/restaurant/dishes-create.blade.php` | Form 1col, sticky save bar |
| `pages/restaurant/categories.blade.php` | Grid 2cols mobile, modal bottom sheet |
| `pages/restaurant/customers.blade.php` | Card stack, filtres |
| `pages/restaurant/customer-show.blade.php` | Grid stats 2cols |
| `pages/restaurant/reservations.blade.php` | Card stack mobile |
| `pages/restaurant/reservation-show.blade.php` | 1col mobile |
| `pages/restaurant/ingredients.blade.php` | Card stack, colonnes cachées |
| `pages/restaurant/ingredient-edit.blade.php` | Form 1col |
| `pages/restaurant/ingredient-show.blade.php` | Form 1col |
| `pages/restaurant/ingredient-categories.blade.php` | Grid 2cols |
| `pages/restaurant/suppliers.blade.php` | Card stack |
| `pages/restaurant/supplier-show.blade.php` | 1col mobile |
| `pages/restaurant/stock-report.blade.php` | overflow-x-auto wrapper |
| `pages/restaurant/qrcode.blade.php` | 1col mobile, QR centré |
| `pages/restaurant/hotel-rooms.blade.php` | Grid 1→2→3 cols |
| `pages/restaurant/settings.blade.php` | Form 1col, sticky save |
| `pages/restaurant/subscription.blade.php` | Plans 1col mobile |
| `pages/restaurant/subscription-plans.blade.php` | Plans 1→2→3 cols |
| `pages/restaurant/subscription-invoices.blade.php` | Card stack |
| `pages/restaurant/analytics.blade.php` | Grille 2cols, charts full-width |

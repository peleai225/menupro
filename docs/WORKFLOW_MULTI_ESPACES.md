# Workflow Multi-Espaces — Employés Verrouillés

## Problématique

Un restaurant avec plusieurs espaces (Terrasse, Intérieur, VIP, Chambres) veut que chaque caisse/employé ne voie QUE les commandes et le stock de SON espace, sans pouvoir changer d'espace.

## Solution : `locked_space_id`

### Migration

```sql
ALTER TABLE users ADD COLUMN locked_space_id BIGINT UNSIGNED NULL;
ALTER TABLE users ADD FOREIGN KEY (locked_space_id) REFERENCES restaurant_spaces(id) ON DELETE SET NULL;
```

**Fichier** : `database/migrations/2026_07_25_100000_add_locked_space_id_to_users_table.php`

### Modèle User

**Relation ajoutée** :
```php
public function lockedSpace(): BelongsTo
{
    return $this->belongsTo(RestaurantSpace::class, 'locked_space_id');
}
```

**Guarded** : `locked_space_id` ne peut pas être assigné en mass-assignment (comme `role` et `restaurant_id`).

### Middleware `SetRestaurantScope`

**Logique** :
1. Si l'utilisateur a `locked_space_id` défini → `session('current_space_id')` est **forcé** à cette valeur
2. Sinon → comportement normal (session libre)
3. Variable partagée aux vues : `$isSpaceLocked` (boolean)

**Code** :
```php
if ($user && $user->locked_space_id) {
    // Employé verrouillé → forcer l'espace
    $currentSpaceId = $user->locked_space_id;
    session(['current_space_id' => $currentSpaceId]);
} else {
    // Utilisateur normal → session libre
    $currentSpaceId = session('current_space_id');
    // Validation...
}
view()->share('isSpaceLocked', $user && $user->locked_space_id !== null);
```

### Composant `space-selector`

Le selector d'espace est **masqué** pour les employés verrouillés :

```blade
@php
    $isLocked = $isSpaceLocked ?? false;
@endphp
@if($activeSpaces->isNotEmpty() && !$isLocked)
    <!-- Selector affiché -->
@endif
```

## Usage

### 1. Admin assigne un employé à un espace

Dans `/dashboard/equipe`, l'admin peut :
- Créer/modifier un employé
- Lui assigner un `locked_space_id`

**Code exemple** :
```php
$user->locked_space_id = $request->input('locked_space_id');
$user->save();
```

### 2. Employé verrouillé se connecte

- Il voit **uniquement** les commandes/stock de son espace
- Le selector d'espace est masqué
- `session('current_space_id')` est forcé à `locked_space_id`

### 3. Admin/Owner (sans `locked_space_id`)

- Peut switcher entre tous les espaces via le selector
- Comportement normal

## Filtres d'espace

**Orders** : `Order::forSpace(session('current_space_id'))`  
**Dishes** : `Dish::forSpace(session('current_space_id'))`  
**Stock** : Déjà scopé automatiquement via `BelongsToRestaurant` + filters par espace

## TODO

- [ ] Ajouter UI dans `/dashboard/equipe` pour assigner `locked_space_id`
- [ ] Tester avec un employé verrouillé
- [ ] Badge visuel pour employés verrouillés dans la liste d'équipe

## Avantages

✅ **Sécurité** : Employé ne peut pas voir les autres espaces  
✅ **Simplicité** : Une seule colonne `locked_space_id`  
✅ **Flexibilité** : Admin peut lock/unlock à tout moment  
✅ **Performance** : Pas de tables intermédiaires

## Cas d'usage

- **Maquis VIP** : Caisse Terrasse vs Caisse Intérieur
- **Hôtel** : Réceptionniste Bâtiment A vs Bâtiment B
- **Complexe** : Bar vs Restaurant vs Piscine

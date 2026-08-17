# MenuPro — Guide de Design

> Ce document définit **pourquoi** on fait les choix visuels qu'on fait, pas seulement quels tokens existent.
> Tout nouveau composant ou écran doit respecter ces intentions.

---

## Philosophie

MenuPro est une plateforme B2B pour des restaurateurs africains. Le design doit communiquer trois choses simultanément :

1. **Sérieux** — c'est un outil professionnel, pas une app de livraison grand public
2. **Énergie** — l'orange ancre une identité forte, mémorable, différenciante
3. **Clarté** — les restaurateurs sont souvent sur mobile, stressés, pressés. L'UI doit répondre sans friction

Les références directes : **Linear** (densité et rigueur), **Stripe** (confiance et hiérarchie), **Vercel** (dark mode premium), **Raycast** (micro-interactions et polish).

---

## 1. Couleur

### L'orange est rare, pas dominant

`#D45E0C` est la couleur de signature. Elle doit **attirer l'œil exactement là où une action est attendue**. Si elle est partout, elle ne sert plus à rien.

**DO** — Orange sur le CTA principal, le lien actif dans la sidebar, un badge de statut important
**DON'T** — Orange sur les titres de section, les icônes décoratives, les bordures de cartes

```
// Correct : un seul point focal orange par zone
[Tableau de bord]  →  bouton "Nouvelle commande" en orange

// Incorrect : dilution de l'attention
[Tableau de bord]  →  titre orange + icônes orange + badge orange + bouton orange
```

### Les neutres portent 80% de l'UI

Inspiré de **Linear** : le fond, les cartes, les textes secondaires sont tous dans la gamme `neutral`. La couleur n'intervient qu'en signal.

| Surface | Valeur | Quand |
|---------|--------|-------|
| Fond de page (public dark) | `#080808` | Hero, sections principales |
| Fond de section alternée | `#0f0f0f` | Sections secondaires |
| Fond de page (admin light) | `#faf9f7` | Super-admin, mode clair |
| Carte admin | `#ffffff` | Contenu au-dessus du fond |
| Texte principal dark | `white` | Titres, labels |
| Texte secondaire dark | `neutral-400` | Descriptions, méta-infos |
| Texte principal light | `neutral-900` | Corps admin |
| Texte secondaire light | `neutral-500` | Labels, placeholders |

> Règle : ne jamais mettre du texte `neutral-700` sur fond `neutral-900`. Le contraste minimum acceptable est 4.5:1.

### Accent = urgence seulement

Le corail `#f43f5e` (`.accent`) est réservé aux états critiques : suppression, erreur, alerte non-résolue. Pas pour de la décoration.

---

## 2. Typographie

### Deux contextes, deux personnalités

**Site public (marketing)** → **Bricolage Grotesque**
Chargée uniquement sur la homepage. Utiliser pour les `h1` et `h2` de hero. Grasse (`font-extrabold`), grand corps (`text-5xl` minimum). C'est la voix de marque.

**Partout ailleurs** → **DM Sans**
Interface, dashboard, formulaires, emails. Lisible à toutes les tailles, excellent en poids `500` et `600`.

### Échelle typographique stricte

| Niveau | Classe Tailwind | Usage |
|--------|----------------|-------|
| Hero H1 | `text-5xl lg:text-[4.6rem] font-extrabold` | Un seul par page |
| Section H2 | `text-3xl lg:text-4xl font-bold` | Titre de section |
| Card H3 | `text-xl font-semibold` | Titre de carte |
| Label UI | `text-sm font-medium` | Champs, nav |
| Corps | `text-base` / `text-sm` | Paragraphes, listes |
| Meta | `text-xs text-neutral-500` | Dates, IDs, badges |

**DO** — Sauter des niveaux dans la hiérarchie (H1 → H3) pour créer du contraste visuel
**DON'T** — Avoir 4 tailles de texte identiques côte à côte — chaque niveau doit être immédiatement reconnaissable

### Gradient de texte — une fois par écran maximum

`.gt` (orange → `#FF8C42`) et `.gt-gold` sont des accents visuels forts. Les utiliser sur **un seul mot-clé** dans le titre hero. Pas sur une phrase entière.

```html
<!-- DO -->
<h1>La plateforme <span class="gt">#1 pour</span> les restaurants</h1>

<!-- DON'T -->
<h1 class="gt">La plateforme numéro 1 pour les restaurants de Côte d'Ivoire</h1>
```

---

## 3. Espacement

### La règle des 4px

Tout espacement est un multiple de 4px. Tailwind l'applique nativement (`p-4` = 16px, `gap-6` = 24px, etc.).

```
4px   → micro séparations, entre icône et label
8px   → intérieur d'un badge, padding d'un tag
12px  → padding d'un bouton compact
16px  → padding standard d'un bouton, d'une carte compacte
24px  → gap entre éléments dans une liste
32px  → padding d'une section de formulaire
48px  → espacement entre sections d'une page admin
80px  → espacement entre sections du site public
```

### Plus d'espace = plus de luxe

Inspiré de **Stripe** : les sections bien aérées communiquent la confiance. Sur le site public, les sections doivent avoir au minimum `py-24` (96px). Sur les pages admin, les cartes méritent `p-6` minimum.

**DON'T** — Compresser pour "tout faire rentrer". Si l'écran est surchargé, supprimer des éléments plutôt que réduire l'espacement.

---

## 4. Composants

### Boutons — un seul CTA primaire par zone

Inspiré de **Linear** : la page répond à une question implicite — "quelle est l'action principale ici ?". Un seul bouton `.btn-primary` ou `.btn-glow` par zone visible. Les autres actions sont secondaires (`.btn-outline`, `.btn-ghost`).

**Hiérarchie des boutons :**
```
btn-glow / btn-primary  →  action principale (une seule par section)
btn-secondary           →  action secondaire confirmée
btn-outline             →  action alternative
btn-ghost               →  action tertiaire, liens discrets
```

**DO** — Grouper les actions secondaires dans un menu déroulant si elles sont nombreuses
**DON'T** — Avoir deux boutons `btn-primary` côte à côte. Si c'est tentant, remettre en question l'architecture de l'écran.

### Cartes — élévation = importance

Inspiré de **Stripe Dashboard** : les cartes ne sont pas toutes égales. L'ombre communique la hiérarchie.

```
fond de page          →  aucune ombre
.card (shadow-card)   →  information standard
.card-interactive     →  information cliquable, appelle à l'action
shadow-elevated       →  modal, dropdown, élément au-dessus du reste
```

**DO** — Utiliser `hover:-translate-y-1 shadow-elevated` uniquement sur les éléments vraiment cliquables
**DON'T** — Ajouter `shadow-elevated` à des cartes statiques pour les "faire ressortir" — ça crée un faux appel à l'action

### Formulaires — un champ à la fois mentalement

Regrouper les champs par intention, pas par type. Un bloc `<fieldset>` ou une `<section>` par concept.

```html
<!-- DO : regroupement logique -->
<section>Informations de contact</section>
<section>Adresse de livraison</section>
<section>Préférences</section>

<!-- DON'T : liste plate de 15 champs -->
<div>nom prénom email tel adresse ville code postal...</div>
```

---

## 5. Animations

### L'animation justifie une action, elle ne décore pas

Inspiré de **Raycast** : chaque transition a une raison d'être.

| Action | Animation appropriée |
|--------|---------------------|
| Apparition au scroll | `.fu` + `.fu.in` (translateY + opacity, 0.65s) |
| Élément cliquable au hover | `scale(1.02)` ou `-translate-y-1` |
| Chargement de données | Skeleton shimmer |
| Toast / notification | Slide depuis la droite (`toast-slide-in`) |
| Modal qui s'ouvre | `scale-in` + `fade-in` |
| Carousel d'images | Opacity + scale(1.04) cross-fade, 0.8s |
| Sidebar toggle | `transition-all duration-300` sur la margin |

**DO** — Animer la **transition d'état** (loading → chargé, caché → visible)
**DON'T** — Animer en boucle des éléments qui ne font rien (`.animate-float` sur un logo statique = distraction)

### Durées

```
150ms  →  hover de couleur (quasi-instantané)
200ms  →  hover de position (translate, scale)
300ms  →  ouverture d'un élément (dropdown, sidebar)
500ms  →  entrée de page, reveal au scroll
800ms  →  carousel, transitions longues et intentionnelles
```

**DON'T** — Dépasser 500ms pour des interactions récurrentes (menus, boutons). L'utilisateur attend.

---

## 6. Dark mode

### Site public — toujours dark

Le site marketing est permanent dark (`#080808`). Ne jamais introduire un mode clair sur les pages publiques. L'identité premium repose sur ce fond profond.

### Admin restaurant — light

Les interfaces de travail (dashboard, commandes, menus) sont en mode clair. Les restaurateurs gèrent leur activité sur des durées longues — le light mode est moins fatigant.

### Super admin — dark sidebar, fond clair, toggle disponible

Le super-admin utilise le pattern **Vercel** : sidebar sombre (`#1a1714`) sur fond de page clair (`#faf9f7`). Le toggle dark complet est disponible via `localStorage['sa-dark']` → classe `.sa-dark` sur le wrapper.

> Ne jamais mélanger les deux systèmes. Les variables `--sa-*` sont strictement isolées du dark mode global Tailwind.

---

## 7. Iconographie

Utiliser **Heroicons** (outline par défaut, solid pour les états actifs). Taille standard : `w-5 h-5` dans les boutons, `w-6 h-6` dans les nav items, `w-8 h-8` dans les cartes de stats.

**DO** — Icône dans un conteneur coloré pour les cartes de statistiques (`.stat-card-icon`)
**DON'T** — Icônes de tailles différentes dans la même liste de navigation

---

## 8. Mobile

Tout composant est conçu mobile-first. Les grilles passent de 1 colonne sur mobile à 2 ou 3 sur desktop (`grid-cols-1 md:grid-cols-2 lg:grid-cols-3`).

Points de rupture clés :
```
xs  480px   →  très petit mobile (ajout custom dans @theme)
sm  640px   →  mobile standard
md  768px   →  tablette
lg  1024px  →  desktop (la majorité des décisions de layout)
xl  1280px  →  grand écran
```

Le sidebar admin passe en drawer mobile (`.lg:hidden`) avec overlay et animation `x-transition`.

---

## 9. Les 5 erreurs à ne pas commettre

1. **Trop d'orange** — dilue la couleur d'action. Maximum 2-3 éléments orange visibles simultanément.
2. **Cartes avec shadow-elevated partout** — l'élévation perd son sens si tous les niveaux sont au même niveau.
3. **Texte sans contraste suffisant** — `neutral-600` sur `neutral-900` = illisible. Toujours tester le ratio.
4. **Animations sur tous les éléments** — `.animate-float` sur 6 éléments d'une même section = bazar visuel.
5. **Mélanger `.fd` (Bricolage Grotesque) dans l'admin** — cette police appartient au site public. L'admin doit rester sobre avec DM Sans.
